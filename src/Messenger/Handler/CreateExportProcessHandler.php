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

namespace Sylius\GridImportExport\Messenger\Handler;

use Sylius\GridImportExport\Entity\ProcessInterface;
use Sylius\GridImportExport\Factory\ProcessFactoryInterface;
use Sylius\GridImportExport\Manager\BatchedExportDataManagerInterface;
use Sylius\GridImportExport\Messenger\Command\CreateExportProcess;
use Sylius\GridImportExport\Messenger\Command\ExportCommand;
use Sylius\GridImportExport\Messenger\Stamp\ExportBatchCounterStamp;
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
        protected int $batchSize = 100,
    ) {
    }

    public function __invoke(CreateExportProcess $command): void
    {
        $process = $this->processFactory->createExportProcess($command);

        $this->batchedDataManager->createStorage($process);

        $batchesCount = (int) ceil(count($process->getResourceIds()) / $this->batchSize);
        $process->setBatchesCount($batchesCount);

        $this->processRepository->add($process);

        foreach (array_chunk($process->getResourceIds(), $this->batchSize) as $batch) {
            $this->messageBus->dispatch(new ExportCommand(
                processId: $process->getUuid(),
                resourceIds: $batch,
            ), [new ExportBatchCounterStamp()]);
        }
    }
}
