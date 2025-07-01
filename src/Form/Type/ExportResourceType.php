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

namespace Sylius\GridImportExport\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

final class ExportResourceType extends AbstractType
{
    public function __construct(
        private readonly ChoiceLoaderInterface $choiceLoader,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('format', ChoiceType::class, [
                'label' => 'sylius_grid_import_export.grid.form.format',
                'choice_loader' => $this->choiceLoader,
            ])
            ->add('currentPage', CheckboxType::class, [
                'label' => 'sylius_grid_import_export.grid.form.current_page',
            ])
            ->add('resourceClass', HiddenType::class)
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_import_export_resource_export';
    }
}
