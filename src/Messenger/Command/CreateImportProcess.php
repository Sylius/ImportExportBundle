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

namespace Sylius\ImportExport\Messenger\Command;

class CreateImportProcess
{
    /**
     * @param array<mixed> $parameters
     */
    public function __construct(
        public string $resource,
        public string $format,
        public string $filePath,
        public array $parameters,
    ) {
    }
}
