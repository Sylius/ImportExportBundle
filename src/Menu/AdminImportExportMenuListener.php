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

namespace Sylius\GridImportExport\Menu;

use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final readonly class AdminImportExportMenuListener
{
    public function __construct(private MenuReorderInterface $menuReorder)
    {
    }

    public function buildMenu(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $exportSection = $menu
            ->addChild('import_export')
            ->setLabel('sylius_grid_import_export.ui.import_export')
            ->setLabelAttribute('icon', 'tabler:arrows-left-right')
        ;

        $this->addExportItem($exportSection);

        $this->menuReorder->reorder($menu, 'import_export', 'marketing');
    }

    public function addExportItem(ItemInterface $item): void
    {
        $item
            ->addChild('processes', [
                'route' => 'sylius_grid_import_export_admin_process_export_index',
            ])
            ->setLabel('sylius_grid_import_export.ui.process_exports')
        ;
    }
}
