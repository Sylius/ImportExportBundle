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

namespace Sylius\ImportExport\Form\ChoiceLoader;

use Sylius\ImportExport\Exporter\ExporterInterface;
use Symfony\Component\Form\ChoiceList\Loader\AbstractChoiceLoader;

final class ExportFormatsChoiceLoader extends AbstractChoiceLoader
{
    /** @var array<string, string> */
    private array $formats = [];

    /** @param iterable<string, ExporterInterface> $exporters */
    public function __construct(iterable $exporters)
    {
        foreach ($exporters as $format => $exporter) {
            $this->formats[$format] = $format;
        }
    }

    protected function loadChoices(): iterable
    {
        return $this->formats;
    }
}
