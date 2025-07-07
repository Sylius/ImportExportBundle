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

namespace Sylius\GridImportExport\Factory;

use Sylius\GridImportExport\Entity\ExportProcessInterface;
use Sylius\GridImportExport\Entity\ProcessInterface;
use Sylius\GridImportExport\Messenger\Command\CreateExportProcess;
use Sylius\Resource\Factory\FactoryInterface;

/** @extends FactoryInterface<ProcessInterface> */
interface ProcessFactoryInterface extends FactoryInterface
{
    public function createExportProcess(CreateExportProcess $command): ExportProcessInterface;
}
