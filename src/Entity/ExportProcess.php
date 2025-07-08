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

class ExportProcess extends Process implements ExportProcessInterface
{
    protected string $format;

    protected string $grid;

    protected array $parameters = [];

    protected array $resourceIds = [];

    protected int $batchesCount = 0;

    protected ?string $temporaryDataStorage = null;

    public function getType(): string
    {
        return ExportProcessInterface::TYPE;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function getGrid(): string
    {
        return $this->grid;
    }

    public function setGrid(string $grid): void
    {
        $this->grid = $grid;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getResourceIds(): array
    {
        return $this->resourceIds;
    }

    public function setResourceIds(array $resourceIds): void
    {
        $this->resourceIds = $resourceIds;
    }

    public function getBatchesCount(): int
    {
        return $this->batchesCount;
    }

    public function setBatchesCount(int $count): void
    {
        $this->batchesCount = $count;
    }

    public function getTemporaryDataStorage(): ?string
    {
        return $this->temporaryDataStorage;
    }

    public function setTemporaryDataStorage(?string $storage): void
    {
        $this->temporaryDataStorage = $storage;
    }
}
