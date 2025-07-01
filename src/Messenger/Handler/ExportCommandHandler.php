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
use Sylius\GridImportExport\Exception\ExportFailedException;
use Sylius\GridImportExport\Factory\ProcessFactoryInterface;
use Sylius\GridImportExport\Messenger\Command\ExportCommand;
use Sylius\GridImportExport\Provider\Registry\ResourceDataProviderRegistryInterface;
use Sylius\GridImportExport\Resolver\ExporterResolverInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Metadata\RegistryInterface;

class ExportCommandHandler
{
    /**
     * @param RepositoryInterface<ProcessInterface> $processRepository
     */
    public function __construct(
        public RegistryInterface $metadataRegistry,
        public ProcessFactoryInterface $processFactory,
        public RepositoryInterface $processRepository,
        public ResourceDataProviderRegistryInterface $dataProviderRegistry,
        public ExporterResolverInterface $exporterResolver,
    ) {
    }

    public function __invoke(ExportCommand $command): void
    {
        $resolver = $this->exporterResolver->resolve($command->format);

        $process = $this->processFactory->createFromExportCommand($command);

        $this->processRepository->add($process);

        $resourceMetadata = $this->metadataRegistry->get($command->resource);

        $data = $this->dataProviderRegistry
            ->getProvider($resourceMetadata)
            ->getData($resourceMetadata, $command->grid, $command->resourceIds, $command->parameters)
        ;

        try {
            $outputPath = $resolver->export($data);

            $process->setStatus('success');
            $process->setOutput($outputPath);
        } catch (ExportFailedException $e) {
            $process->setStatus('failed');
            $process->setErrorMessage($e->getMessage());
        } catch (\Throwable $e) {
            $process->setStatus('failed');
            $process->setErrorMessage($e->getMessage());
        }
    }
}
