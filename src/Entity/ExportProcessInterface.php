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

interface ExportProcessInterface extends ProcessInterface
{
    public const TYPE = 'export';

    public function getFormat(): string;

    public function setFormat(string $format): void;

    public function getResourceIds(): array;

    public function setResourceIds(array $resourceIds): void;

    public function getGrid(): string;

    public function setGrid(string $grid): void;

    public function getParameters(): array;

    public function setParameters(array $parameters): void;

    public function getBatchesCount(): int;

    public function setBatchesCount(int $count): void;

    public function getTemporaryDataStorage(): ?string;

    public function setTemporaryDataStorage(?string $storage): void;
}
