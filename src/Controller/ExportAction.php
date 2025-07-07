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

use Sylius\Bundle\ResourceBundle\Controller\ParametersParserInterface;
use Sylius\Component\Grid\Provider\GridProviderInterface;
use Sylius\GridImportExport\Messenger\Command\CreateExportProcess;
use Sylius\GridImportExport\Provider\ResourceIds\ResourcesIdsProviderInterface;
use Sylius\Resource\Metadata\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Messenger\MessageBusInterface;

final class ExportAction
{
    public function __construct(
        private RegistryInterface $metadataRegistry,
        private GridProviderInterface $gridProvider,
        private ParametersParserInterface $parametersParser,
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
        $gridConfiguration = $this->gridProvider->get($grid);

        $resourceIds = $this->resourcesIdsProvider->getResourceIds(
            metadata: $metadata,
            context: ['request' => $request, 'ids' => $data['ids'] ?? []],
        );

        $parameters = $this->parametersParser->parseRequestValues(
            $gridConfiguration->getDriverConfiguration(),
            $request,
        );

        $this->commandBus->dispatch(new CreateExportProcess(
            resource: $metadata->getAlias(),
            format: $format,
            grid: $grid,
            parameters: $parameters,
            resourceIds: $resourceIds,
        ));

        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add('success', 'sylius_grid_import_export.export_started');

        return new RedirectResponse($request->headers->get('referer') ?? '/');
    }
}
