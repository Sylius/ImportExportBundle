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

namespace Sylius\ImportExport\Grid\Listener;

use Sylius\Bundle\CoreBundle\SectionResolver\SectionProviderInterface;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ActionGroupInterface;
use Sylius\Bundle\GridBundle\Doctrine\ORM\Driver as ORMDriver;
use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Definition\ActionGroup;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;
use Sylius\ImportExport\Grid\Checker\ExportableCheckerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class ImportActionAdminGridListener
{
    private const IMPORT_ACTION_NAME = 'import';

    public function __construct(
        private RequestStack $requestStack,
        private ?SectionProviderInterface $sectionProvider,
        private ExportableCheckerInterface $exportableChecker, // Reuse for import checking
    ) {
    }

    public function addImportActions(GridDefinitionConverterEvent $event): void
    {
        $grid = $event->getGrid();
        if (ORMDriver::NAME !== $grid->getDriver()) {
            return;
        }

        // For now, use same logic as export to determine if import is allowed
        if (
            !$this->exportableChecker->canBeExported($grid, $this->sectionProvider?->getSection()) &&
            !$this->exportableChecker->canBeExported($grid, $this->getRouteSection())
        ) {
            return;
        }

        // Add import action only to main group for now
        $this->addInActionGroup($grid, ActionGroupInterface::MAIN_GROUP);
    }

    private function addInActionGroup(Grid $grid, string $groupName): void
    {
        if (!$grid->hasActionGroup($groupName)) {
            $grid->addActionGroup(ActionGroup::named($groupName));
        }

        $actionGroup = $grid->getActionGroup($groupName);
        if ($actionGroup->hasAction(self::IMPORT_ACTION_NAME)) {
            return;
        }

        $action = Action::fromNameAndType(self::IMPORT_ACTION_NAME, self::IMPORT_ACTION_NAME);

        $actionGroup->addAction($action);
    }

    private function getRouteSection(): ?string
    {
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            return null;
        }

        return $request->attributes->all()['_sylius']['section'] ?? null;
    }
}
