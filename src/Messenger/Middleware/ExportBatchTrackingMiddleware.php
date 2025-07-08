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

namespace Sylius\ImportExport\Messenger\Middleware;

use Sylius\ImportExport\Entity\ExportProcessInterface;
use Sylius\ImportExport\Messenger\Command\ExportCommand;
use Sylius\ImportExport\Messenger\Event\ExportProcessCompleted;
use Sylius\ImportExport\Messenger\Stamp\ExportBatchCounterStamp;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

final class ExportBatchTrackingMiddleware implements MiddlewareInterface
{
    /** @param RepositoryInterface<ExportProcessInterface> $processRepository */
    public function __construct(
        private RepositoryInterface $processRepository,
        private MessageBusInterface $eventBus,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $envelope = $stack->next()->handle($envelope, $stack);

        $stamp = $envelope->last(ExportBatchCounterStamp::class);
        if (null === $stamp) {
            return $envelope;
        }

        $message = $envelope->getMessage();
        if (!$message instanceof ExportCommand) {
            return $envelope;
        }

        $process = $this->processRepository->find($message->processId);
        if (null === $process || $process->getBatchesCount() > 0) {
            return $envelope;
        }

        $this->eventBus->dispatch(
            new ExportProcessCompleted($process->getUuid()),
            [new DispatchAfterCurrentBusStamp()],
        );

        return $envelope;
    }
}
