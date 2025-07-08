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

namespace Sylius\ImportExport\Manager;

use Sylius\ImportExport\Entity\ExportProcessInterface;

interface BatchedExportDataManagerInterface
{
    public function createStorage(ExportProcessInterface $exportProcess): void;

    public function getStorage(ExportProcessInterface $exportProcess): ?string;

    public function setStorage(ExportProcessInterface $exportProcess): void;

    public function resetStorage(ExportProcessInterface $exportProcess): void;

    public function saveBatch(ExportProcessInterface $exportProcess, array $data): void;

    /** @return iterable<array<string, mixed>> */
    public function getBatchedData(ExportProcessInterface $exportProcess): iterable;

    public function deleteBatchedData(ExportProcessInterface $exportProcess): void;
}
