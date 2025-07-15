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

namespace Sylius\ImportExport\Denormalizer;

interface ResourceDenormalizerInterface
{
    public function denormalize(array $data, string $resourceClass): object;

    public function supports(string $resourceClass): bool;
}
