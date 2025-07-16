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
use Symfony\Component\Serializer\Encoder\JsonEncoder;

final class JsonImporter implements ImporterInterface
{
    public const FORMAT = 'json';

    public function __construct(
        private JsonEncoder $jsonEncoder,
    ) {
    }

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

            return $this->jsonEncoder->decode($content, self::FORMAT);
        } catch (\Throwable $exception) {
            throw new ImportFailedException(sprintf('Failed to import from "%s": %s', $filePath, $exception->getMessage()));
        }
    }
}
