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

namespace Sylius\ImportExport\Messenger\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\ImportExport\Denormalizer\DenormalizerRegistryInterface;
use Sylius\ImportExport\Entity\ImportProcessInterface;
use Sylius\ImportExport\Exception\ImportFailedException;
use Sylius\ImportExport\Messenger\Command\ImportCommand;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

class ImportCommandHandler
{
    /** @param RepositoryInterface<ImportProcessInterface> $processRepository */
    public function __construct(
        protected RepositoryInterface $processRepository,
        protected DenormalizerRegistryInterface $denormalizerRegistry,
        protected EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(ImportCommand $command): void
    {
        $process = $this->processRepository->find($command->processId);
        if (null === $process) {
            throw new ImportFailedException(sprintf('Process with uuid "%s" not found.', $command->processId));
        }

        try {
            $importedCount = 0;
            $resourceClass = $process->getResource();
            $denormalizer = $this->denormalizerRegistry->get($resourceClass);

            foreach ($command->batchData as $recordData) {
                $entity = $denormalizer->denormalize($recordData, $resourceClass);
                $this->entityManager->persist($entity);

                ++$importedCount;
            }

            $this->entityManager->flush();

            $process->setBatchesCount($process->getBatchesCount() - 1);
            $process->setImportedCount($process->getImportedCount() + $importedCount);

            if ($process->getBatchesCount() <= 0) {
                $process->setStatus('success');
            }
        } catch (\Throwable $e) {
            $process->setStatus('failed');
            $process->setErrorMessage($e->getMessage());
        }
    }
}
