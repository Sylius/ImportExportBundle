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

namespace Sylius\GridImportExport\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\MappingException;
use Sylius\GridImportExport\Exception\ProviderException;
use Sylius\Resource\Metadata\MetadataInterface;

final class ResourceIdentifierProvider implements ResourceIdentifierProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getIdentifierField(MetadataInterface $metadata): string
    {
        try {
            // TODO: If possible we could add this to resource config or create a custom metadata //
            $resourceDoctrineMetadata = $this->entityManager->getClassMetadata($metadata->getClass('model'));

            return $resourceDoctrineMetadata->getSingleIdentifierFieldName();
        } catch (MappingException $exception) {
            throw new ProviderException('Composite ids are not supported.', previous: $exception);
        }
    }
}
