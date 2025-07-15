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

namespace Sylius\ImportExport\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

final class ImportResourceType extends AbstractType
{
    public function __construct(
        private string $projectDir,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('format', ChoiceType::class, [
                'label' => 'sylius_import_export.grid.form.format',
                'choices' => [
                    'json' => 'json',
                    'csv' => 'csv',
                ],
                'data' => 'json', // Default to JSON for testing
            ])
            ->add('filePath', HiddenType::class, [
                'data' => $this->projectDir . '/var/exported/export.json', // Hardcoded path
            ])
            ->add('resourceClass', HiddenType::class)
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_import_export_resource_import';
    }
}
