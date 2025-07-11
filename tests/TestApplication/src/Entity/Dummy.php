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

namespace Tests\Sylius\ImportExport\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Sylius\ImportExport\Serializer\DefaultSerializationGroups;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[Entity]
#[ORM\Table(name: 'sylius_test_dummy')]
class Dummy implements ResourceInterface
{
    #[ORM\CustomIdGenerator(null)]
    #[ORM\Id()]
    #[ORM\Column(name: 'uuid')]
    #[Groups(DefaultSerializationGroups::EXPORT_GROUP)]
    private string $uuid;

    #[ORM\Column(name: 'text')]
    #[Groups(DefaultSerializationGroups::EXPORT_GROUP)]
    private string $text;

    #[ORM\Column(name: 'counter', type: 'integer')]
    #[Groups(DefaultSerializationGroups::EXPORT_GROUP)]
    private int $counter;

    #[ORM\Column(name: 'config', type: 'json')]
    #[Groups(DefaultSerializationGroups::EXPORT_GROUP)]
    private array $config;

    #[ORM\OneToMany(targetEntity: DummyItem::class, mappedBy: 'dummy', cascade: ['all'], orphanRemoval: true)]
    #[Groups(DefaultSerializationGroups::EXPORT_GROUP)]
    private Collection $dummyItems;

    public function __construct()
    {
        $this->dummyItems = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function setCounter(int $counter): void
    {
        $this->counter = $counter;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getDummyItems(): Collection
    {
        return $this->dummyItems;
    }

    public function addDummyItem(DummyItem $item): void
    {
        if (!$this->hasDummyItem($item)) {
            $this->dummyItems->add($item);
            $item->setDummy($this);
        }
    }

    public function removeDummyItem(DummyItem $item): void
    {
        if ($this->hasDummyItem($item)) {
            $this->dummyItems->removeElement($item);
            $item->setDummy(null);
        }
    }

    public function hasDummyItem(DummyItem $item): bool
    {
        return $this->dummyItems->contains($item);
    }
}
