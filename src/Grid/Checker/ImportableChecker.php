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

namespace Sylius\ImportExport\Grid\Checker;

use Sylius\Component\Grid\Definition\Grid;
use Sylius\Resource\Metadata\RegistryInterface;

final readonly class ImportableChecker implements ImportableCheckerInterface
{
    /** @param array<string, array{validation_groups: string[]}> $importResourcesConfig */
    public function __construct(
        private RegistryInterface $resourceRegistry,
        private array $importResourcesConfig,
    ) {
    }

    public function canBeImported(Grid $grid, object|string|null $section): bool
    {
        $resourceClass = $grid->getDriverConfiguration()['class'] ?? null;
        if (null === $resourceClass) {
            return false;
        }

        $resourceAlias = $this->resourceRegistry->getByClass($resourceClass)->getAlias();

        return isset($this->importResourcesConfig[$resourceAlias]);
    }
}
