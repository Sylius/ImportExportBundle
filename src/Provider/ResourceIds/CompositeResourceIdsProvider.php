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

use Sylius\ImportExport\Exception\ProviderException;
use Sylius\Resource\Metadata\MetadataInterface;

final class CompositeResourceIdsProvider implements ResourcesIdsProviderInterface
{
    /** @param iterable<ResourcesIdsProviderInterface> $resourceIdsProviders */
    public function __construct(private iterable $resourceIdsProviders)
    {
    }

    public function getResourceIds(MetadataInterface $metadata, array $context = []): array
    {
        foreach ($this->resourceIdsProviders as $idsProvider) {
            if ($idsProvider->supports($metadata, $context)) {
                return $idsProvider->getResourceIds($metadata, $context);
            }
        }

        throw new ProviderException(sprintf(
            'There is no resources ids provider for resource %s and context %s',
            $metadata->getAlias(),
            json_encode($context),
        ));
    }

    public function supports(MetadataInterface $metadata, array $context = []): bool
    {
        return true;
    }
}
