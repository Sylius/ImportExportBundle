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

namespace Sylius\ImportExport\Validator;

use Sylius\ImportExport\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportValidator
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    public function validate(object $entity, array $validationGroups): void
    {
        $violations = $this->validator->validate($entity, groups: $validationGroups);

        if (count($violations) > 0) {
            $errorMessages = [];
            foreach ($violations as $violation) {
                $errorMessages[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new ValidationFailedException(sprintf('Validation failed for record: %s', implode(', ', $errorMessages)));
        }
    }
}
