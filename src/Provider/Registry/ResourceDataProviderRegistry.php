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

namespace Sylius\ImportExport\Provider\Registry;

use Sylius\ImportExport\Exception\ProviderException;
use Sylius\ImportExport\Provider\ResourceData\ResourceDataProviderInterface;
use Sylius\Resource\Metadata\MetadataInterface;

final class ResourceDataProviderRegistry implements ResourceDataProviderRegistryInterface
{
    /**
     * @param array<string, array{provider: string, sections: string[]}> $exportResourcesConfig
     * @param iterable<string, ResourceDataProviderInterface> $resourceDataProviders
     */
    public function __construct(
        private array $exportResourcesConfig,
        private iterable $resourceDataProviders,
    ) {
    }

    public function getProvider(MetadataInterface $resourceMetadata): ResourceDataProviderInterface
    {
        $resourceAlias = $resourceMetadata->getAlias();
        $resourceConfig = $this->exportResourcesConfig[$resourceAlias] ?? null;
        if (null === $resourceConfig) {
            throw new ProviderException(sprintf(
                'Provider configuration for resource "%s" is missing',
                $resourceAlias,
            ));
        }

        foreach ($this->resourceDataProviders as $serviceId => $provider) {
            if ($serviceId === $resourceConfig['provider']) {
                return $provider;
            }
        }

        throw new ProviderException(sprintf('There is not data provider for resource "%s"', $resourceAlias));
    }
}
