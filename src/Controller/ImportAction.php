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

use Sylius\ImportExport\Messenger\Command\CreateImportProcess;
use Sylius\ImportExport\Uploader\ImportFileUploader;
use Sylius\Resource\Metadata\RegistryInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class ImportAction
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private MessageBusInterface $commandBus,
        private string $importForm,
        private ImportFileUploader $importFileUploader,
        private RegistryInterface $metadataRegistry,
        private array $importResources,
    ) {
    }

    public function __invoke(Request $request, string $grid): Response
    {
        /** @var Session $session */
        $session = $request->getSession();

        $form = $this->formFactory->create($this->importForm);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                if ($error instanceof FormError) {
                    $errors[] = $error->getMessage();
                }
            }
            $errorMessage = !empty($errors) ? implode(', ', $errors) : 'sylius_import_export.import_form_invalid';
            $session->getFlashBag()->add('error', $errorMessage);

            return new RedirectResponse($request->headers->get('referer') ?? '/');
        }

        $data = $form->getData();
        $resourceClass = $data['resourceClass'];

        /** @var UploadedFile $file */
        $file = $data['file'];

        try {
            $format = $this->importFileUploader->getFormatFromMimeType($file->getMimeType());
            $filePath = $this->importFileUploader->upload($file);

            $metadata = $this->metadataRegistry->getByClass($resourceClass);
            $resourceAlias = $metadata->getAlias();

            $parameters = [];
            if (isset($this->importResources[$resourceAlias])) {
                $parameters['validation_groups'] = $this->importResources[$resourceAlias]['validation_groups'] ?? ['Default'];
            }

            $this->commandBus->dispatch(new CreateImportProcess(
                resource: $resourceAlias,
                format: $format,
                filePath: $filePath,
                parameters: $parameters,
            ));

            $session->getFlashBag()->add('success', 'sylius_import_export.import_started');
        } catch (\Throwable $e) {
            $session->getFlashBag()->add('error', 'sylius_import_export.failed');
        }

        return new RedirectResponse($request->headers->get('referer') ?? '/');
    }
}
