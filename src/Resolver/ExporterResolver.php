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

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Sylius\ImportExport\Exporter\ExporterInterface;

final readonly class ExporterResolver implements ExporterResolverInterface
{
    public function __construct(private ContainerInterface $exporters)
    {
    }

    public function resolve(string $format): ExporterInterface
    {
        if (!$this->exporters->has($format)) {
            throw new InvalidArgumentException(sprintf('Exporter for "%s" format was not found.', $format));
        }

        $exporter = $this->exporters->get($format);

        if (!$exporter instanceof ExporterInterface) {
            throw new InvalidArgumentException(sprintf('Exporter for "%s" format was not found.', $format));
        }

        return $exporter;
    }
}
