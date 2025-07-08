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

namespace Sylius\ImportExport\Grid\Definition;

use Sylius\Component\Grid\Definition\ArrayToDefinitionConverter;
use Sylius\Component\Grid\Definition\ArrayToDefinitionConverterInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/** @internal */
final class CommonEventDispatchingArrayToDefinitionConverter implements ArrayToDefinitionConverterInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private ArrayToDefinitionConverterInterface $decorated,
    ) {
    }

    public function convert(string $code, array $configuration): Grid
    {
        $grid = $this->decorated->convert($code, $configuration);

        $this->eventDispatcher->dispatch(
            new GridDefinitionConverterEvent($grid),
            sprintf(ArrayToDefinitionConverter::EVENT_NAME, 'common'),
        );

        return $grid;
    }
}
