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

namespace Sylius\ImportExport\Entity;

class ImportProcess extends Process implements ImportProcessInterface
{
    protected string $format;

    protected string $filePath;

    protected array $parameters = [];

    protected int $batchesCount = 0;

    protected int $importedCount = 0;

    protected ?string $temporaryDataStorage = null;

    protected int $toBeImportedCount = 0;

    public function getType(): string
    {
        return ImportProcessInterface::TYPE;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getBatchesCount(): int
    {
        return $this->batchesCount;
    }

    public function setBatchesCount(int $count): void
    {
        $this->batchesCount = $count;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function setImportedCount(int $count): void
    {
        $this->importedCount = $count;
    }

    public function getTemporaryDataStorage(): ?string
    {
        return $this->temporaryDataStorage;
    }

    public function setTemporaryDataStorage(?string $storage): void
    {
        $this->temporaryDataStorage = $storage;
    }

    public function getToBeImportedCount(): int
    {
        return $this->toBeImportedCount;
    }

    public function setToBeImportedCount(int $count): void
    {
        $this->toBeImportedCount = $count;
    }
}
