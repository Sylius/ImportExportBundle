<?php

declare(strict_types=1);

namespace Sylius\ImportExport\Provider\Parameters;

use Sylius\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\Request;

interface GridExportParametersProviderInterface
{
    /** @return array<string, mixed> */
    public function getParameters(MetadataInterface $metadata, string $gridName, Request $request): array;
}
