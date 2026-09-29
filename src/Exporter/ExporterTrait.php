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

trait ExporterTrait
{
    /**
     * @param (int[]|string[])[] $data
     *
     * @return string[]
     */
    private function getHeaders(array $data): array
    {
        $firstRow = reset($data) ?: [];
        $keys = array_keys($firstRow);

        return array_map(static fn (int|string $key) => ucfirst((string) $key), $keys);
    }
}
