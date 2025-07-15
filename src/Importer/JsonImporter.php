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

namespace Sylius\ImportExport\Importer;

use Sylius\ImportExport\Exception\ImportFailedException;

final class JsonImporter implements ImporterInterface
{
    public const FORMAT = 'json';

    public function getConfig(): array
    {
        return [
            'format' => self::FORMAT,
        ];
    }

    public function import(string $filePath): array
    {
        try {
            $content = file_get_contents($filePath);

            if (false === $content) {
                throw new \InvalidArgumentException();
            }

            return json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            throw new ImportFailedException(sprintf('Failed to import from "%s": %s', $filePath, $exception->getMessage()));
        }
    }
}
