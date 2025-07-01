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

namespace Sylius\GridImportExport\Grid\Listener;

use Sylius\Bundle\GridBundle\Builder\ActionGroup\ActionGroupInterface;
use Sylius\Bundle\GridBundle\Doctrine\ORM\Driver as ORMDriver;
use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Definition\ActionGroup;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;
use Sylius\GridImportExport\Grid\Checker\ExportableCheckerInterface;

final readonly class ExportActionAdminGridListener
{
    private const EXPORT_ACTION_NAME = 'export';

    public function __construct(
        private ExportableCheckerInterface $exportableChecker,
    ) {
    }

    public function addExportActions(GridDefinitionConverterEvent $event): void
    {
        $grid = $event->getGrid();
        if (ORMDriver::NAME !== $grid->getDriver()) {
            return;
        }

        if (!$this->exportableChecker->canBeExported($grid)) {
            return;
        }

        $this->addInActionGroup($grid, ActionGroupInterface::MAIN_GROUP);
        $this->addInActionGroup($grid, ActionGroupInterface::BULK_GROUP);
    }

    private function addInActionGroup(Grid $grid, string $groupName): void
    {
        if (!$grid->hasActionGroup($groupName)) {
            $grid->addActionGroup(ActionGroup::named($groupName));
        }

        $actionGroup = $grid->getActionGroup($groupName);
        if ($actionGroup->hasAction(self::EXPORT_ACTION_NAME)) {
            return;
        }

        $action = Action::fromNameAndType(self::EXPORT_ACTION_NAME, self::EXPORT_ACTION_NAME);

        $actionGroup->addAction($action);
    }
}
