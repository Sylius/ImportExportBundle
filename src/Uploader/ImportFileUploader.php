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

namespace Sylius\ImportExport\Uploader;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class ImportFileUploader
{
    public function __construct(
        private string $importFilesDirectory,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        if (!is_dir($this->importFilesDirectory)) {
            mkdir($this->importFilesDirectory, 0755, true);
        }

        $fileName = uniqid() . '_' . $file->getClientOriginalName();
        $filePath = $this->importFilesDirectory . '/' . $fileName;

        $file->move($this->importFilesDirectory, $fileName);

        return $filePath;
    }

    public function getFormatFromMimeType(?string $mimeType): string
    {
        return match ($mimeType) {
            'application/json' => 'json',
            'text/csv', 'text/plain' => 'csv',
            default => 'json',
        };
    }
}
