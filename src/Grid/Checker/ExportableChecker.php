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

namespace Sylius\GridImportExport\Grid\Checker;

use Sylius\Component\Grid\Definition\Grid;
use Sylius\Resource\Metadata\RegistryInterface;

final readonly class ExportableChecker implements ExportableCheckerInterface
{
    /** @param array<string, array{provider: string, sections: string[]}> $exportResourcesConfig */
    public function __construct(
        private RegistryInterface $resourceRegistry,
        private array $exportResourcesConfig,
    ) {
    }

    public function canBeExported(Grid $grid, object|string|null $section): bool
    {
        $resourceClass = $grid->getDriverConfiguration()['class'] ?? null;
        if (null === $resourceClass) {
            return false;
        }

        $resourceAlias = $this->resourceRegistry->getByClass($resourceClass)->getAlias();

        $resourceConfig = $this->exportResourcesConfig[$resourceAlias] ?? false;
        if (false === $resourceConfig) {
            return false;
        }

        if (null === $section) {
            return true;
        }

        foreach ($resourceConfig['sections'] as $configSection) {
            if (is_a($section, $configSection, true) || $section === $configSection) {
                return true;
            }
        }

        return false;
    }
}
