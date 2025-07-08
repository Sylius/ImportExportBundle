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

namespace Sylius\ImportExport\Factory;

use Sylius\ImportExport\Entity\ExportProcessInterface;
use Sylius\ImportExport\Entity\ProcessInterface;
use Sylius\ImportExport\Messenger\Command\CreateExportProcess;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\Uid\Uuid;

final readonly class ProcessFactory implements ProcessFactoryInterface
{
    /** @param FactoryInterface<ExportProcessInterface> $exportFactory */
    public function __construct(
        private FactoryInterface $exportFactory,
    ) {
    }

    public function createNew(): ProcessInterface
    {
        throw new \InvalidArgumentException();
    }

    public function createExportProcess(CreateExportProcess $command): ExportProcessInterface
    {
        $process = $this->exportFactory->createNew();
        $process->setUuid(Uuid::v7()->toRfc4122());
        $process->setResource($command->resource);
        $process->setGrid($command->grid);
        $process->setFormat($command->format);
        $process->setParameters($command->parameters);
        $process->setResourceIds($command->resourceIds);
        $process->setStatus('processing');

        return $process;
    }
}
