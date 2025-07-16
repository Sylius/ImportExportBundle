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

namespace Sylius\ImportExport\Processor;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\ImportExport\Denormalizer\DenormalizerRegistryInterface;
use Sylius\ImportExport\Entity\ImportProcessInterface;
use Sylius\ImportExport\Validator\ImportValidator;
use Sylius\Resource\Metadata\RegistryInterface;

final readonly class BatchProcessor
{
    public function __construct(
        private DenormalizerRegistryInterface $denormalizerRegistry,
        private EntityManagerInterface $entityManager,
        private RegistryInterface $metadataRegistry,
        private ImportValidator $importValidator,
    ) {
    }

    public function processBatch(ImportProcessInterface $process, array $batchData): int
    {
        $importedCount = 0;
        $resourceMetadata = $this->metadataRegistry->get($process->getResource());
        $resourceClass = $resourceMetadata->getClass('model');
        $denormalizer = $this->denormalizerRegistry->get($resourceClass);
        $validationGroups = $process->getParameters()['validation_groups'] ?? ['sylius'];

        foreach ($batchData as $recordData) {
            $entity = $denormalizer->denormalize($recordData, $resourceClass);

            $this->importValidator->validate($entity, $validationGroups);

            $this->entityManager->persist($entity);
            ++$importedCount;
        }

        $this->entityManager->flush();

        return $importedCount;
    }
}
