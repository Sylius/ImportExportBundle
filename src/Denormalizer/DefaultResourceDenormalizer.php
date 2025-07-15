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

use Sylius\ImportExport\Serializer\ExportAwareItemNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class DefaultResourceDenormalizer implements ResourceDenormalizerInterface
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
    ) {
    }

    public function denormalize(array $data, string $resourceClass): object
    {
        return $this->denormalizer->denormalize(
            $data,
            $resourceClass,
            null,
            [
                ExportAwareItemNormalizer::EXPORT_CONTEXT_KEY => true,
                DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s',
                AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
            ],
        );
    }

    public function supports(string $resourceClass): bool
    {
        return true;
    }
}
