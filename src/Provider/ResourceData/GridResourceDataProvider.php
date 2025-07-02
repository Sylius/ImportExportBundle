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

namespace Sylius\GridImportExport\Provider\ResourceData;

use Sylius\Bundle\GridBundle\Doctrine\ORM\DataSource as ORMDataSource;
use Sylius\Bundle\GridBundle\Doctrine\ORM\Driver as ORMDriver;
use Sylius\Component\Grid\Data\DataSourceProviderInterface;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Grid\Provider\GridProviderInterface;
use Sylius\GridImportExport\Exception\ProviderException;
use Sylius\Resource\Metadata\MetadataInterface;

final class GridResourceDataProvider implements ResourceDataProviderInterface
{
    public function __construct(
        private GridProviderInterface $gridProvider,
        private DataSourceProviderInterface $gridDataSourceProvider,
    ) {
    }

    public function getData(MetadataInterface $resource, string $gridCode, array $resourceIds, array $parameters): array
    {
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
        $dataSource->restrict($dataSource->getExpressionBuilder()->in('id', $resourceIds));

        return $dataSource->getQueryBuilder()->getQuery()->getArrayResult();
    }
}
