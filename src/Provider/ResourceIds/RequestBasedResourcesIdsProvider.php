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

namespace Sylius\GridImportExport\Provider\ResourceIds;

use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourcesCollectionProviderInterface;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridView;
use Sylius\GridImportExport\Exception\ProviderException;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\Request;

final class RequestBasedResourcesIdsProvider implements ResourcesIdsProviderInterface
{
    public function __construct(
        private RequestConfigurationFactoryInterface $requestConfigurationFactory,
        private ResourcesCollectionProviderInterface $resourcesCollectionProvider,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /** @param array<mixed>|array{request: Request} $context */
    public function getResourceIds(MetadataInterface $metadata, array $context = []): array
    {
        if (!$this->supports($metadata, $context)) {
            throw new ProviderException('Request is missing from the context.');
        }

        return $this->doGetResourceIds($metadata, $context['request']);
    }

    public function supports(MetadataInterface $metadata, array $context = []): bool
    {
        return isset($context['request']) && $context['request'] instanceof Request;
    }

    private function doGetResourceIds(MetadataInterface $metadata, Request $request): array
    {
        $resourceClass = $metadata->getClass('model');
        /** @var RepositoryInterface $repository */
        $repository = $this->entityManager->getRepository($resourceClass);

        $requestConfiguration = $this->requestConfigurationFactory->create($metadata, $request);
        $resources = $this->resourcesCollectionProvider->get($requestConfiguration, $repository);
        if (!$resources instanceof ResourceGridView) {
            throw new \RuntimeException(sprintf('Expected ResourceGridView, got %s', get_class($resources)));
        }

        // TODO: We might need a custom Grid DataSource to skip pagerfanta creation and unnecessary iteration
        //       Apply the filters how it's done normally and return only ids with a quick select
        $paginator = $resources->getData();
        if (!$paginator instanceof Pagerfanta) {
            throw new \RuntimeException(sprintf(
                'Only pagerfanta data is supported, got %s',
                is_object($paginator) ? get_class($paginator) : gettype($paginator),
            ));
        }

        $ids = [];
        foreach ($paginator->autoPagingIterator() as $item) {
            $ids[] = (string) $item->getId();
        }

        return $ids;
    }
}
