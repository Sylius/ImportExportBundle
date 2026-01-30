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

use Sylius\ImportExport\Exception\ExportFailedException;

final class JsonExporter extends AbstractExporter
{
    public const FORMAT = 'json';

    protected function getFormat(): string
    {
        return self::FORMAT;
    }

    public function supportsBatchedExport(): bool
    {
        return false;
    }

    public function export(array $data, array $context): string
    {
        $filename = $this->generateFilePath(self::FORMAT);

        try {
            $json = json_encode($data, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
            file_put_contents($filename, $json);
        } catch (\Throwable $exception) {
            throw new ExportFailedException($exception->getMessage());
        }

        return $filename;
    }
}
