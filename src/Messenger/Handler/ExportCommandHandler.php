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
use Sylius\ImportExport\Manager\BatchedExportDataManagerInterface;
use Sylius\ImportExport\Messenger\Command\ExportCommand;
use Sylius\ImportExport\Provider\Registry\ResourceDataProviderRegistryInterface;
use Sylius\ImportExport\Resolver\ExporterResolverInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Metadata\RegistryInterface;

class ExportCommandHandler
{
    /** @param RepositoryInterface<ExportProcessInterface> $processRepository */
    public function __construct(
        protected RegistryInterface $metadataRegistry,
        protected RepositoryInterface $processRepository,
        protected ResourceDataProviderRegistryInterface $dataProviderRegistry,
        protected ExporterResolverInterface $exporterResolver,
        protected BatchedExportDataManagerInterface $batchManager,
    ) {
    }

    public function __invoke(ExportCommand $command): void
    {
        $process = $this->processRepository->find($command->processId);
        if (null === $process) {
            throw new ExportFailedException(sprintf('Process with uuid "%s" not found.', $command->processId));
        }

        $resourceMetadata = $this->metadataRegistry->get($process->getResource());

        $data = $this->dataProviderRegistry
            ->getProvider($resourceMetadata)
            ->getData($resourceMetadata, $command->resourceIds, $process->getParameters())
        ;

        $process->setBatchesCount($process->getBatchesCount() - 1);

        $exporter = $this->exporterResolver->resolve($process->getFormat());

        if (null !== $this->batchManager->getStorage($process) && !$exporter->supportsBatchedExport()) {
            $this->batchManager->saveBatch($process, $data);

            return;
        }

        try {
            $outputPath = $exporter->export(
                $data,
                [
                    'resourceAlias' => $process->getResource(),
                    'batchIndex' => $command->batchIndex,
                    'exportFilename' => $process->getOutput(),
                ],
            );

            $process->setStatus('success');
            $process->setOutput($outputPath);
        } catch (\Throwable $e) {
            $process->setStatus('failed');
            $process->setErrorMessage($e->getMessage());
        }
    }
}
