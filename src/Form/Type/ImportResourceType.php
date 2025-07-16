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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ImportResourceType extends AbstractType
{
    public function __construct(
        private string $fileMaxSize,
        private array $allowedMimeTypes,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'sylius_import_export.grid.form.file',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'sylius_import_export.validation.import.file.not_blank',
                        'groups' => ['sylius'],
                    ]),
                    new File([
                        'maxSize' => $this->fileMaxSize,
                        'mimeTypes' => $this->allowedMimeTypes,
                        'mimeTypesMessage' => 'sylius_import_export.validation.import.file.invalid_type',
                        'maxSizeMessage' => 'sylius_import_export.validation.import.file.max_size',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('resourceClass', HiddenType::class)
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_import_export_resource_import';
    }
}
