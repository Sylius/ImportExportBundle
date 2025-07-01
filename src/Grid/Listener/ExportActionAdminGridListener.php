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

use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Definition\ActionGroup;
use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;
use Sylius\Resource\Metadata\RegistryInterface;
use Sylius\Bundle\GridBundle\Doctrine\ORM\Driver as ORMDriver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class ExportActionAdminGridListener
{
    /**
     * @param array<array-key, string> $allowedSections
     * @param array<array-key, string> $allowedResources
     */
    public function __construct(
        private RequestStack $requestStack,
        private RegistryInterface $resourceRegistry,
        private array $allowedSections,
        private array $allowedResources,
    ) {
    }

    public function addExportMainAction(GridDefinitionConverterEvent $event): void
    {
        $grid = $event->getGrid();
        if (ORMDriver::NAME !== $grid->getDriver()) {
            return;
        }

        if (!$this->canBeExported($grid->getDriverConfiguration())) {
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

    private function canBeExported(array $driverConfiguration): bool
    {
        $resourceClass = $driverConfiguration['class'] ?? null;
        if (null === $resourceClass) {
            return false;
        }

        $request = $this->requestStack->getMainRequest();
        if (!$request instanceof Request) {
            return false;
        }

        if (!$request->attributes->has('_sylius')) {
            return false;
        }

        $syliusAttributes = $request->attributes->all()['_sylius'];
        if (!in_array($syliusAttributes['section'] ?? null, $this->allowedSections)) {
            return false;
        }

        $resourceMetadata = $this->resourceRegistry->getByClass($resourceClass);

        return in_array($resourceMetadata->getAlias(), $this->allowedResources);
    }
}
