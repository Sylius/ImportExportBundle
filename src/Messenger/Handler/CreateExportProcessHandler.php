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

use Sylius\ImportExport\Entity\ProcessInterface;
use Sylius\ImportExport\Factory\ProcessFactoryInterface;
use Sylius\ImportExport\Manager\BatchedExportDataManagerInterface;
use Sylius\ImportExport\Messenger\Command\CreateExportProcess;
use Sylius\ImportExport\Messenger\Command\ExportCommand;
use Sylius\ImportExport\Messenger\Stamp\ExportBatchCounterStamp;
use Sylius\ImportExport\Resolver\ExporterResolverInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateExportProcessHandler
{
    /**
     * @param RepositoryInterface<ProcessInterface> $processRepository
     * @param int<1, max> $batchSize
     */
    public function __construct(
        protected ProcessFactoryInterface $processFactory,
        protected RepositoryInterface $processRepository,
        protected MessageBusInterface $messageBus,
        protected BatchedExportDataManagerInterface $batchedDataManager,
        protected ExporterResolverInterface $exporterResolver,
        protected int $batchSize = 100,
    ) {
    }

    public function __invoke(CreateExportProcess $command): void
    {
        $process = $this->processFactory->createExportProcess($command);
        $exporter = $this->exporterResolver->resolve($process->getFormat());

        if (!$exporter->supportsBatchedExport()) {
            $this->batchedDataManager->createStorage($process);
        }

        $batchesCount = (int) ceil(count($process->getResourceIds()) / $this->batchSize);
        $process->setBatchesCount($batchesCount);

        $this->processRepository->add($process);

        foreach (array_chunk($process->getResourceIds(), $this->batchSize) as $batchIndex => $batch) {
            $this->messageBus->dispatch(new ExportCommand(
                processId: $process->getUuid(),
                resourceIds: $batch,
                batchIndex: $batchIndex,
            ), [new ExportBatchCounterStamp()]);
        }
    }
}
