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
use Sylius\ImportExport\Messenger\Command\CreateImportProcess;
use Sylius\ImportExport\Messenger\Command\ImportCommand;
use Sylius\ImportExport\Resolver\ImporterResolverInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateImportProcessHandler
{
    /**
     * @param RepositoryInterface<ProcessInterface> $processRepository
     * @param int<1, max> $batchSize
     */
    public function __construct(
        protected ProcessFactoryInterface $processFactory,
        protected RepositoryInterface $processRepository,
        protected MessageBusInterface $messageBus,
        protected ImporterResolverInterface $importerResolver,
        protected int $batchSize = 100,
    ) {
    }

    public function __invoke(CreateImportProcess $command): void
    {
        $process = $this->processFactory->createImportProcess($command);

        $importer = $this->importerResolver->resolve($command->format);
        $data = $importer->import($command->filePath);

        $batches = array_chunk($data, $this->batchSize);
        $process->setBatchesCount(count($batches));

        $this->processRepository->add($process);

        foreach ($batches as $batchData) {
            $this->messageBus->dispatch(new ImportCommand(
                processId: $process->getUuid(),
                batchData: $batchData,
            ));
        }
    }
}
