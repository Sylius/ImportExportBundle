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

namespace Sylius\ImportExport\Provider\ResourceIds;

use Sylius\Resource\Metadata\MetadataInterface;

final class StaticResourceIdsProvider implements ResourcesIdsProviderInterface
{
    /** @param array<mixed>|array{ids: array<string|int>} $context */
    public function getResourceIds(MetadataInterface $metadata, array $context = []): array
    {
        return $context['ids'];
    }

    public function supports(MetadataInterface $metadata, array $context = []): bool
    {
        return isset($context['ids']) && [] !== $context['ids'];
    }
}
