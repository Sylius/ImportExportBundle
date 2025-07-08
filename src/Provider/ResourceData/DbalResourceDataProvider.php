<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\ImportExport\Provider\ResourceData;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sylius\ImportExport\Exception\ProviderException;
use Sylius\ImportExport\Provider\ResourceIdentifierProviderInterface;
use Sylius\Resource\Metadata\MetadataInterface;

final class DbalResourceDataProvider implements ResourceDataProviderInterface
{
    /** @var array<class-string, array<string, string>> */
    private static array $resourceFieldsMetadata = [];

    public function __construct(
        private readonly ResourceIdentifierProviderInterface $identifierProvider,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getData(MetadataInterface $resource, string $gridCode, array $resourceIds, array $parameters): array
    {
        $resourceIdentifierField = $this->identifierProvider->getIdentifierField($resource);
        $metadata = $this->getResourceMetadata($resource->getClass('model'));
        $scalarFieldsMetadata = $this->getResourceScalarFieldsData($metadata, $resource->getClass('model'));
        if (empty($scalarFieldsMetadata)) {
            return [];
        }

        return $this->fetch(
            $this->entityManager->getConnection(),
            $metadata,
            $resourceIdentifierField,
            $resourceIds,
            $scalarFieldsMetadata,
        );
    }

    private function fetch(
        Connection $connection,
        ClassMetadata $metadata,
        string $resourceIdentifierField,
        array $resourceIds,
        array $scalarFieldsMetadata,
    ): array {
        $selectParts = [];
        foreach ($scalarFieldsMetadata as $fieldName => $columnName) {
            $selectParts[] = sprintf('o.%s AS %s', $columnName, $fieldName);
        }

        $query = sprintf(
            'SELECT %s FROM %s o WHERE o.%s IN (:ids)',
            implode(', ', $selectParts),
            $metadata->getTableName(),
            $resourceIdentifierField,
        );

        try {
            return $connection->fetchAllAssociative(
                $query,
                ['ids' => $resourceIds],
                ['ids' => ArrayParameterType::STRING],
            );
        } catch (Exception $exception) {
            throw new ProviderException(
                sprintf('Failed to fetch data for resource "%s" with IDs: %s', $metadata->getName(), implode(', ', $resourceIds)),
                previous: $exception,
            );
        }
    }

    private function getResourceMetadata(string $resource): ClassMetadata
    {
        return $this->entityManager->getClassMetadata($resource);
    }

    /** @param class-string $resource */
    private function getResourceScalarFieldsData(ClassMetadata $metadata, string $resource): array
    {
        if (isset(self::$resourceFieldsMetadata[$resource])) {
            return self::$resourceFieldsMetadata[$resource];
        }

        /** @var array<string, string> $scalarFieldsMetadata */
        $scalarFieldsMetadata = [];
        foreach ($metadata->getFieldNames() as $fieldName) {
            if ($metadata->hasAssociation($fieldName)) {
                continue;
            }

            $scalarFieldsMetadata[$fieldName] = $metadata->getColumnName($fieldName);
        }

        self::$resourceFieldsMetadata[$resource] = $scalarFieldsMetadata;

        return $scalarFieldsMetadata;
    }
}
