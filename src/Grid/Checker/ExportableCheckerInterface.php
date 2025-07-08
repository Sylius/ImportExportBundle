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

interface ExportableCheckerInterface
{
    public function canBeExported(Grid $grid, object|string|null $section): bool;
}
