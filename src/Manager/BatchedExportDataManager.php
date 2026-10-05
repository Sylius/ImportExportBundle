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

final class BatchedExportDataManager implements BatchedExportDataManagerInterface
{
    private string $temporaryExportsDirectory;

    public function __construct(string $temporaryDirectory)
    {
        $this->temporaryExportsDirectory = sprintf('%s/ongoing/', $temporaryDirectory);
    }

    public function createStorage(ExportProcessInterface $exportProcess): void
    {
        $ongoingProcessDirectory = $this->temporaryExportsDirectory . '/' . $exportProcess->getUuid();
        $exportProcess->setTemporaryDataStorage($ongoingProcessDirectory);

        if (!is_dir($ongoingProcessDirectory)) {
            mkdir($ongoingProcessDirectory, recursive: true);
        }
    }

    public function getStorage(ExportProcessInterface $exportProcess): ?string
    {
        return $exportProcess->getTemporaryDataStorage();
    }

    public function setStorage(ExportProcessInterface $exportProcess): void
    {
        $exportProcess->setTemporaryDataStorage(
            $this->temporaryExportsDirectory . '/' . $exportProcess->getUuid(),
        );
    }

    public function resetStorage(ExportProcessInterface $exportProcess): void
    {
        $exportProcess->setTemporaryDataStorage(null);
    }

    public function saveBatch(ExportProcessInterface $exportProcess, array $data): void
    {
        if (null === $this->getStorage($exportProcess)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'No storage set on process with uuid "%s"',
                    $exportProcess->getUuid(),
                ),
            );
        }

        file_put_contents(
            rtrim($this->getStorage($exportProcess), '/') . '/' . uniqid() . '.json',
            json_encode($data, \JSON_THROW_ON_ERROR),
        );
    }

    public function getBatchedData(ExportProcessInterface $exportProcess): iterable
    {
        if (null === $storage = $this->getStorage($exportProcess)) {
            throw new \InvalidArgumentException(sprintf('No storage set on process with uuid "%s"', $exportProcess->getUuid()));
        }

        foreach ($this->getFilesIn($storage) as $file) {
            $filePath = $storage . '/' . $file;
            if (is_file($filePath)) {
                yield json_decode((string) file_get_contents($filePath), true);
            }
        }
    }

    public function deleteBatchedData(ExportProcessInterface $exportProcess): void
    {
        $storage = $this->getStorage($exportProcess);
        if (null === $storage || !is_dir($storage)) {
            return;
        }

        foreach ($this->getFilesIn($storage) as $file) {
            unlink($storage . '/' . $file);
        }

        rmdir($storage);
    }

    private function getFilesIn(string $directory): array
    {
        $files = scandir($directory);

        return array_filter($files ?: [], static function ($file) {
            return str_ends_with($file, '.json');
        });
    }
}
