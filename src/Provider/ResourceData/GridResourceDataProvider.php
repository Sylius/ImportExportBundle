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

use Sylius\Bundle\GridBundle\Doctrine\ORM\DataSource as ORMDataSource;
use Sylius\Bundle\GridBundle\Doctrine\ORM\Driver as ORMDriver;
use Sylius\Component\Grid\Data\DataSourceProviderInterface;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Grid\Provider\GridProviderInterface;
use Sylius\ImportExport\Exception\ProviderException;
use Sylius\ImportExport\Provider\ResourceIdentifierProviderInterface;
use Sylius\ImportExport\Serializer\DefaultSerializationGroups;
use Sylius\ImportExport\Serializer\ExportAwareItemNormalizer;
use Sylius\Resource\Metadata\MetadataInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class GridResourceDataProvider implements ResourceDataProviderInterface
{
    public function __construct(
        private ResourceIdentifierProviderInterface $identifierProvider,
        private GridProviderInterface $gridProvider,
        private DataSourceProviderInterface $gridDataSourceProvider,
        private NormalizerInterface $serializer,
    ) {
    }

    public function getData(MetadataInterface $resource, string $gridCode, array $resourceIds, array $parameters): array
    {
        $identifier = $this->identifierProvider->getIdentifierField($resource);

        $grid = $this->gridProvider->get($gridCode);
        if (ORMDriver::NAME !== $grid->getDriver()) {
            throw new ProviderException(sprintf(
                'This provider supports only the "%s" grid driver, "%s" configured for grid "%s".',
                ORMDriver::NAME,
                $grid->getDriver(),
                $gridCode,
            ));
        }

        $grid->setDriverConfiguration($parameters);

        /** @var ORMDataSource $dataSource */
        $dataSource = $this->gridDataSourceProvider->getDataSource($grid, new Parameters($parameters));
        $dataSource->restrict($dataSource->getExpressionBuilder()->in($identifier, $resourceIds));

        $rawData = $dataSource->getQueryBuilder()->getQuery()->getResult();

        /** @phpstan-ignore-next-line */
        return $this->serializer->normalize($rawData, context: [
            ExportAwareItemNormalizer::EXPORT_CONTEXT_KEY => true,
            'groups' => $parameters['serialization_groups'] ?? [DefaultSerializationGroups::EXPORT_GROUP],
        ]);
    }
}
