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

abstract class AbstractExporter implements ExporterInterface
{
    public function __construct(protected readonly string $exportDirectory)
    {
    }

    abstract protected function getFormat(): string;

    public function getConfig(): array
    {
        return [
            'format' => $this->getFormat(),
        ];
    }

    protected function generateFilePath(string $extension): string
    {
        return sprintf(
            '%s/%s.%s',
            rtrim($this->exportDirectory, '/'),
            uniqid('export_', true),
            $extension,
        );
    }
}
