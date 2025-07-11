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

namespace Tests\Sylius\ImportExport\Functional;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Webmozart\Assert\Assert;

abstract class FunctionalTestCase extends WebTestCase
{
    protected ?EntityManagerInterface $entityManager;

    #[Before]
    public function setUpDatabase(): void
    {
        if (isset($_SERVER['IS_DOCTRINE_ORM_SUPPORTED']) && $_SERVER['IS_DOCTRINE_ORM_SUPPORTED']) {
            $container = static::getContainer();
            Assert::notNull($container);

            /** @var EntityManagerInterface $entityManager */
            $entityManager = $container->get('doctrine.orm.entity_manager');
            Assert::notNull($entityManager);

            $this->entityManager = $entityManager;
            $this->entityManager->getConnection()->getNativeConnection();

            $this->purgeDatabase();
        }
    }

    protected function purgeDatabase(): void
    {
        $purger = new ORMPurger($this->getEntityManager());
        $purger->purge();

        $this->getEntityManager()->clear();
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        $entityManager = $this->entityManager;
        if (null === $entityManager || !$entityManager->getConnection()->isConnected()) {
            static::fail('Could not establish test database connection.');
        }

        return $entityManager;
    }
}
