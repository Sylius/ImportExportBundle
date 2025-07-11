<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;


return function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests/Functional',
        __DIR__ . '/tests/TestApplication/src',
    ]);

    $ecsConfig->import('vendor/sylius-labs/coding-standard/ecs.php');

    $ecsConfig->ruleWithConfiguration(HeaderCommentFixer::class, [
        'location' => 'after_open',
        'header' =>
            'This file is part of the Sylius package.

(c) Sylius Sp. z o.o.

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.',
    ]);
};
