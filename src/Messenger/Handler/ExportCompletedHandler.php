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

use Sylius\ImportExport\Entity\ExportProcessInterface;
use Sylius\ImportExport\Exception\ExportFailedException;
use Sylius\ImportExport\Exporter\ExporterInterface;
use Sylius\ImportExport\Manager\BatchedExportDataManagerInterface;
use Sylius\ImportExport\Messenger\Event\ExportProcessCompleted;
use Sylius\ImportExport\Resolver\ExporterResolverInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

class ExportCompletedHandler
{
    /** @param RepositoryInterface<ExportProcessInterface> $processRepository */
    public function __construct(
        private RepositoryInterface $processRepository,
        private ExporterResolverInterface $exporterResolver,
        private BatchedExportDataManagerInterface $batchedDataManager,
    ) {
    }

    public function __invoke(ExportProcessCompleted $event): void
    {
        $process = $this->processRepository->find($event->processId);
        if (null === $process) {
            throw new ExportFailedException(sprintf('Process with uuid "%s" not found.', $event->processId));
        }

        $exporter = $this->exporterResolver->resolve($process->getFormat());

        try {
            if (!$exporter->supportsBatchedExport()) {
                $outputPath = $this->exportBatchedData($process, $exporter);

                $process->setOutput($outputPath);
            }
        } catch (\Throwable $e) {
            $process->setStatus('failed');
            $process->setErrorMessage($e->getMessage());
        }

        $process->setStatus('success');

        $this->batchedDataManager->deleteBatchedData($process);

        $process->setBatchesCount(0);
        $this->batchedDataManager->resetStorage($process);

        $this->processRepository->add($process);
    }

    private function exportBatchedData(ExportProcessInterface $process, ExporterInterface $exporter): string
    {
        $batchedData = $this->batchedDataManager->getBatchedData($process);
        $data = array_merge(...iterator_to_array($batchedData));

        return $exporter->export(
            $data,
            [
                'resourceAlias' => $process->getResource(),
                'batchIndex' => 0,
            ],
        );
    }
}
