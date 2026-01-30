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

namespace Sylius\ImportExport\Exporter;

interface ExporterInterface
{
    public function getConfig(): array;

    public function supportsBatchedExport(): bool;

    public function export(array $data, array $context): string;
}
