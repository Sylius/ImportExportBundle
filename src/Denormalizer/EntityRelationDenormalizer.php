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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class EntityRelationDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param class-string $type
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        $existingEntity = $this->findExistingEntity($type, $data);
        if (null !== $existingEntity) {
            if (!$this->entityManager->contains($existingEntity)) {
                try {
                    $this->entityManager->refresh($existingEntity);
                } catch (\Exception) {
                    $this->entityManager->persist($existingEntity);
                }
            }

            return $existingEntity;
        }

        $context['entity_relation_denormalizer_skip'] = true;

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (isset($context['entity_relation_denormalizer_skip'])) {
            return false;
        }

        if (!is_array($data) || !class_exists($type)) {
            return false;
        }

        try {
            $this->entityManager->getClassMetadata($type);

            return isset($data['id']) || isset($data['code']);
        } catch (\Exception) {
            return false;
        }
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    /**
     * @param class-string $entityClass
     */
    private function findExistingEntity(string $entityClass, array $data): ?object
    {
        $repository = $this->entityManager->getRepository($entityClass);

        if (isset($data['code'])) {
            return $repository->findOneBy(['code' => $data['code']]);
        }

        if (isset($data['id'])) {
            return $repository->find($data['id']);
        }

        return null;
    }
}
