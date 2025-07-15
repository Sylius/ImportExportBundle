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

namespace Sylius\ImportExport\Denormalizer;

final class DenormalizerRegistry implements DenormalizerRegistryInterface
{
    /** @var ResourceDenormalizerInterface[] */
    private array $denormalizers = [];

    private ResourceDenormalizerInterface $defaultDenormalizer;

    /**
     * @param iterable<ResourceDenormalizerInterface> $denormalizers
     */
    public function __construct(ResourceDenormalizerInterface $defaultDenormalizer, iterable $denormalizers = [])
    {
        $this->defaultDenormalizer = $defaultDenormalizer;

        foreach ($denormalizers as $denormalizer) {
            $this->register($denormalizer);
        }
    }

    public function register(ResourceDenormalizerInterface $denormalizer): void
    {
        $this->denormalizers[] = $denormalizer;
    }

    public function get(string $resourceClass): ResourceDenormalizerInterface
    {
        foreach ($this->denormalizers as $denormalizer) {
            if ($denormalizer->supports($resourceClass)) {
                return $denormalizer;
            }
        }

        return $this->defaultDenormalizer;
    }

    public function has(string $resourceClass): bool
    {
        foreach ($this->denormalizers as $denormalizer) {
            if ($denormalizer->supports($resourceClass)) {
                return true;
            }
        }

        return true;
    }
}
