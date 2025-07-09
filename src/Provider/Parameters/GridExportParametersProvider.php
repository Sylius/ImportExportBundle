<?php

declare(strict_types=1);

namespace Sylius\ImportExport\Provider\Parameters;

use Sylius\Bundle\ResourceBundle\Controller\ParametersParserInterface;
use Sylius\Component\Grid\Provider\GridProviderInterface;
use Sylius\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\Request;

final class GridExportParametersProvider implements GridExportParametersProviderInterface
{
    /** @param array<string, array{serialization_group: string}> $resourceExportConfiguration */
    public function __construct(
        private GridProviderInterface $gridProvider,
        private ParametersParserInterface $parametersParser,
        private array $resourceExportConfiguration,
    ) {
    }

    public function getParameters(MetadataInterface $metadata, string $gridName, Request $request): array
    {
        $gridConfiguration = $this->gridProvider->get($gridName);

        $resourceExportConfiguration = $this->resourceExportConfiguration[$metadata->getAlias()] ?? [];
        $serializationGroup = $resourceExportConfiguration['serialization_group'] ?? 'sylius_import_export.export';

        $parameters = $this->parametersParser->parseRequestValues(
            $gridConfiguration->getDriverConfiguration(),
            $request,
        );

        return array_merge(
            $parameters,
            ['serialization_group' => $serializationGroup],
        );
    }
}
