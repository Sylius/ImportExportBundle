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

namespace Sylius\ImportExport\Twig\Component;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

class ExportResourceFormComponent
{
    use ComponentWithFormTrait;

    #[ExposeInTemplate]
    public string $resourceClass;

    public function __construct(
        protected FormFactoryInterface $formFactory,
        protected string $formClass,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->create($this->formClass, ['resourceClass' => $this->resourceClass]);
    }
}
