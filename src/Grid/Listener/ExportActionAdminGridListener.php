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

use Sylius\Bundle\GridBundle\Doctrine\ORM\Driver as ORMDriver;
use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Definition\ActionGroup;
use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;
use Sylius\GridImportExport\Grid\Checker\ExportableCheckerInterface;

final readonly class ExportActionAdminGridListener
{
    public function __construct(
        private ExportableCheckerInterface $exportableChecker,
    ) {
    }

    public function addExportMainAction(GridDefinitionConverterEvent $event): void
    {
        $grid = $event->getGrid();
        if (ORMDriver::NAME !== $grid->getDriver()) {
            return;
        }

        if (!$this->exportableChecker->canBeExported($grid)) {
            return;
        }

        if (!$grid->hasActionGroup('main')) {
            $grid->addActionGroup(ActionGroup::named('main'));
        }

        $actionGroup = $grid->getActionGroup('main');
        if ($actionGroup->hasAction('export')) {
            return;
        }

        $action = Action::fromNameAndType('export', 'export');

        $actionGroup->addAction($action);
    }
}
