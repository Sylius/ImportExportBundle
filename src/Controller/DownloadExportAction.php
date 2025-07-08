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

namespace Sylius\ImportExport\Controller;

use Sylius\ImportExport\Entity\ExportProcessInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DownloadExportAction
{
    /** @param RepositoryInterface<ExportProcessInterface> $exportProcessRepository */
    public function __construct(
        private RepositoryInterface $exportProcessRepository,
    ) {
    }

    public function __invoke(string $uuid): Response
    {
        $process = $this->exportProcessRepository->find($uuid);
        if (null === $process) {
            throw new NotFoundHttpException(sprintf('Export process "%s" not found', $uuid));
        }

        if ('success' !== $process->getStatus()) {
            throw new NotFoundHttpException('Export file is not ready for download');
        }

        $filePath = $process->getOutput();
        if (empty($filePath)) {
            throw new NotFoundHttpException('No output file available for this export');
        }

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException(sprintf('Export file "%s" does not exist', basename($filePath)));
        }

        $response = new BinaryFileResponse($filePath);

        // Sanitize filename by removing invalid characters
        $sanitizedResource = str_replace(['/', '\\', '.'], '_', $process->getResource());
        $sanitizedUuid = str_replace(['/', '\\'], '_', $process->getUuid());

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('export_%s_%s.%s', $sanitizedResource, $sanitizedUuid, $process->getFormat()),
        );

        return $response;
    }
}
