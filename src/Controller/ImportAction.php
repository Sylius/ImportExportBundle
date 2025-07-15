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
use Sylius\Resource\Metadata\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Messenger\MessageBusInterface;

final class ImportAction
{
    public function __construct(
        private RegistryInterface $metadataRegistry,
        private FormFactoryInterface $formFactory,
        private MessageBusInterface $commandBus,
        private string $importForm,
    ) {
    }

    public function __invoke(Request $request, string $grid): Response
    {
        $request->attributes->set('_sylius', array_merge($request->attributes->get('_sylius', []), ['grid' => $grid]));

        $form = $this->formFactory->create($this->importForm);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            /** @var Session $session */
            $session = $request->getSession();
            $session->getFlashBag()->add('error', 'sylius_import_export.import_form_invalid');

            return new RedirectResponse($request->headers->get('referer') ?? '/');
        }

        $data = $form->getData();
        $format = $data['format'];
        $filePath = $data['filePath'];
        $resourceClass = $data['resourceClass'];

        // Get metadata to find the resource alias
        $metadata = $this->metadataRegistry->getByClass($resourceClass);

        try {
            $this->commandBus->dispatch(new CreateImportProcess(
                resource: $resourceClass, // Pass the actual class, not the alias
                format: $format,
                filePath: $filePath,
                parameters: [], // Empty for now
            ));

            /** @var Session $session */
            $session = $request->getSession();
            $session->getFlashBag()->add('success', 'sylius_import_export.import_started');
        } catch (\Throwable $e) {
            /** @var Session $session */
            $session = $request->getSession();
            $session->getFlashBag()->add('error', 'sylius_import_export.import_failed: ' . $e->getMessage());
        }

        return new RedirectResponse($request->headers->get('referer') ?? '/');
    }
}
