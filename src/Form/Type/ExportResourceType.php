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
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
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
            ->add('ids', HiddenType::class)
            ->add('resourceClass', HiddenType::class)
        ;

        $builder->get('ids')->addModelTransformer(new CallbackTransformer(
            static fn (?array $ids): string => $ids ? implode(',', $ids) : '',
            static fn (?string $ids): array => $ids ? array_filter(array_map('trim', explode(',', $ids))) : [],
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_import_export_resource_export';
    }
}
