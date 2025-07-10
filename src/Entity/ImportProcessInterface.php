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

interface ImportProcessInterface extends ProcessInterface
{
    public const TYPE = 'import';

    public function getFormat(): string;

    public function setFormat(string $format): void;

    public function getFilePath(): string;

    public function setFilePath(string $filePath): void;

    public function getParameters(): array;

    public function setParameters(array $parameters): void;

    public function getBatchesCount(): int;

    public function setBatchesCount(int $count): void;

    public function getImportedCount(): int;

    public function setImportedCount(int $count): void;

    public function getTemporaryDataStorage(): ?string;

    public function setTemporaryDataStorage(?string $storage): void;
}
