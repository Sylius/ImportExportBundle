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

use League\Csv\Writer;
use Sylius\ImportExport\Exception\ExportFailedException;

final class CsvExporter extends AbstractExporter
{
    use ExporterTrait;

    public const FORMAT = 'csv';

    public function __construct(
        string $exportDirectory,
        private readonly string $delimiter = ',',
        private readonly bool $writeHeaders = false,
    ) {
        parent::__construct($exportDirectory);
    }

    protected function getFormat(): string
    {
        return self::FORMAT;
    }

    public function supportsBatchedExport(): bool
    {
        return true;
    }

    public function export(array $data, array $context): string
    {
        $data = $this->normalizeValues($data);
        $filename = $context['exportFilename'] ?? $this->generateFilePath(self::FORMAT);
        $batchIndex = $context['batchIndex'] ?? 0;

        try {
            $writer = Writer::from($filename, 'a');
            $writer->setDelimiter($this->delimiter);

            if ((0 == $batchIndex) && $this->writeHeaders) {
                $headers = $this->getHeaders($data);
                $writer->insertOne($headers);
            }

            $writer->insertAll($data);
        } catch (\Throwable $exception) {
            throw new ExportFailedException($exception->getMessage());
        }

        return $filename;
    }

    // TODO: Temporary bugfix, should be extracted into a serializer/normalizer system
    private function normalizeValues(array $data): array
    {
        $dataCount = count($data);
        for ($i = 0; $i < $dataCount; ++$i) {
            foreach ($data[$i] as $field => $value) {
                if ($value instanceof \DateTime) {
                    $data[$i][$field] = $value->format(\DATE_ATOM);

                    continue;
                }
                if (is_object($value) || is_array($value)) {
                    $data[$i][$field] = json_encode($value);
                }
            }
        }

        return $data;
    }
}
