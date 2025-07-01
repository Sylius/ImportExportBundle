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

namespace Sylius\GridImportExport\Entity;

use Sylius\Resource\Model\ResourceInterface;
use Sylius\Resource\Model\TimestampableInterface;

interface ProcessInterface extends ResourceInterface, TimestampableInterface
{
    public const TYPE_EXPORT = 'export';

    public const TYPE_IMPORT = 'import';

    public function getUuid(): string;

    public function setUuid(string $uuid): void;

    public function getType(): string;

    public function setType(string $type): void;

    public function getFormat(): string;

    public function setFormat(string $format): void;

    public function getStatus(): string;

    public function setStatus(string $status): void;

    public function getResource(): string;

    public function setResource(string $resource): void;

    public function getResourceIds(): array;

    public function setResourceIds(array $resourceIds): void;

    public function getOutput(): string;

    public function setOutput(string $output): void;

    public function getErrorMessage(): ?string;

    public function setErrorMessage(?string $errorMessage): void;
}
