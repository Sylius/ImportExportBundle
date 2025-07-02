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
use Sylius\Bundle\GridBundle\Doctrine\ORM\DataSource as ORMDataSource;
use Sylius\Bundle\GridBundle\Doctrine\ORM\Driver as ORMDriver;
use Sylius\Bundle\ResourceBundle\Controller\ParametersParserInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourcesCollectionProviderInterface;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridView;
use Sylius\Component\Grid\Data\DataSourceProviderInterface;
use Sylius\Component\Grid\Parameters;
use Sylius\GridImportExport\Exception\ProviderException;
use Sylius\GridImportExport\Provider\ResourceIdentifierProviderInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\Request;

final class RequestBasedResourcesIdsProvider implements ResourcesIdsProviderInterface
{
    public function __construct(
        private RequestConfigurationFactoryInterface $requestConfigurationFactory,
        private ResourcesCollectionProviderInterface $resourcesCollectionProvider,
        private DataSourceProviderInterface $gridDataSourceProvider,
        private ParametersParserInterface $parametersParser,
        private ResourceIdentifierProviderInterface $identifierProvider,
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
        $resourceIdentifier = $this->identifierProvider->getIdentifierField($metadata);

        $resourceClass = $metadata->getClass('model');
        /** @var RepositoryInterface $repository */
        $repository = $this->entityManager->getRepository($resourceClass);

        $requestConfiguration = $this->requestConfigurationFactory->create($metadata, $request);
        $resources = $this->resourcesCollectionProvider->get($requestConfiguration, $repository);
        if (!$resources instanceof ResourceGridView) {
            throw new \RuntimeException(sprintf('Expected ResourceGridView, got %s', get_class($resources)));
        }
        // TODO: Extract actual grid based ids resolving per dbal and orm //
        //       Maybe also move this validation to a compiler pass instead of having it done in runtime //
        $grid = $resources->getDefinition();
        if (ORMDriver::NAME !== $grid->getDriver()) {
            throw new ProviderException(sprintf(
                'Request based resource ids provider is only usage in grids with "%s" driver',
                ORMDriver::NAME,
            ));
        }

        $parameters = $this->parametersParser->parseRequestValues(
            $grid->getDriverConfiguration(),
            $request,
        );

        $grid->setDriverConfiguration($parameters);

        /** @var ORMDataSource $dataSource */
        $dataSource = $this->gridDataSourceProvider->getDataSource($grid, new Parameters($parameters));

        $queryBuilder = $dataSource->getQueryBuilder();
        $rootAlias = $queryBuilder->getRootAliases()[0];

        return $queryBuilder
            ->select(sprintf('%s.%s', $rootAlias, $resourceIdentifier))
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }
}
