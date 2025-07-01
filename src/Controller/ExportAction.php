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

namespace Sylius\GridImportExport\Controller;

use Sylius\GridImportExport\Messenger\Command\ExportCommand;
use Sylius\GridImportExport\Provider\ResourcesIdsProviderInterface;
use Sylius\Resource\Metadata\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

final class ExportAction
{
    public function __construct(
        private RegistryInterface $metadataRegistry,
        private ResourcesIdsProviderInterface $resourcesIdsProvider,
        private FormFactoryInterface $formFactory,
        private MessageBusInterface $commandBus,
        private string $exportForm,
    ) {
    }

    public function __invoke(Request $request, string $grid): Response
    {
        $request->attributes->set('_sylius', array_merge($request->attributes->get('_sylius', []), ['grid' => $grid]));

        $form = $this->formFactory->create($this->exportForm);
        $form->handleRequest($request);

        $data = $form->getData();
        $format = $data['format'];
        $resourceClass = $data['resourceClass'];

        $metadata = $this->metadataRegistry->getByClass($resourceClass);

        $resourceIds = $this->resourcesIdsProvider->getResourceIds(
            metadata: $metadata,
            context: ['request' => $request, 'currentPage' => $data['currentPage'] ?? false],
        );

        $this->commandBus->dispatch(new ExportCommand(
            resource: $resourceClass,
            format: $format,
            resourceIds: $resourceIds,
        ));

        return new RedirectResponse($request->headers->get('referer') ?? '/');
    }
}
