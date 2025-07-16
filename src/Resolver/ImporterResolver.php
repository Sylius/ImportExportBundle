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

namespace Sylius\ImportExport\Resolver;

use Psr\Container\ContainerInterface;
use Sylius\ImportExport\Importer\ImporterInterface;

final readonly class ImporterResolver implements ImporterResolverInterface
{
    public function __construct(private ContainerInterface $importers)
    {
    }

    public function resolve(string $format): ImporterInterface
    {
        if (!$this->importers->has($format)) {
            throw new \InvalidArgumentException(sprintf('Importer for "%s" format was not found.', $format));
        }

        return $this->importers->get($format);
    }
}
