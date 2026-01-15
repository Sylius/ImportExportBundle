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

namespace Sylius\ImportExport\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\ResourceAccessCheckerInterface;
use ApiPlatform\Metadata\ResourceClassResolverInterface;
use ApiPlatform\Serializer\AbstractItemNormalizer;
use ApiPlatform\Serializer\ItemNormalizer;
use ApiPlatform\Serializer\TagCollectorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @see ItemNormalizer
 *
 * @internal Decorates the original to prevent generating iris for exportable items.
 */
class ExportAwareItemNormalizer extends ItemNormalizer
{
    public const EXPORT_CONTEXT_KEY = 'sylius_import_export.export';

    public function __construct(
        PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory,
        PropertyMetadataFactoryInterface $propertyMetadataFactory,
        IriConverterInterface $iriConverter,
        ResourceClassResolverInterface $resourceClassResolver,
        ?PropertyAccessorInterface $propertyAccessor = null,
        ?NameConverterInterface $nameConverter = null,
        ?ClassMetadataFactoryInterface $classMetadataFactory = null,
        ?LoggerInterface $logger = null,
        ?ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory = null,
        ?ResourceAccessCheckerInterface $resourceAccessChecker = null,
        array $defaultContext = [],
        ?TagCollectorInterface $tagCollector = null,
        private ?AbstractItemNormalizer $decorated = null,
    ) {
        parent::__construct(
            $propertyNameCollectionFactory,
            $propertyMetadataFactory,
            $iriConverter,
            $resourceClassResolver,
            $propertyAccessor,
            $nameConverter,
            $classMetadataFactory,
            $logger,
            $resourceMetadataFactory,
            $resourceAccessChecker,
            $defaultContext,
            $tagCollector,
        );
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::EXPORT_CONTEXT_KEY])) {
            return false;
        }

        return $this->decorated->supportsNormalization($data, $format, $context);
    }

    public function denormalize(mixed $data, string $class, ?string $format = null, array $context = []): mixed
    {
        return $this->decorated->denormalize($data, $class, $format, $context);
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): bool {
        if (isset($context[self::EXPORT_CONTEXT_KEY])) {
            return false;
        }

        return $this->decorated->supportsDenormalization($data, $type, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        parent::setSerializer($serializer);

        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }
}
