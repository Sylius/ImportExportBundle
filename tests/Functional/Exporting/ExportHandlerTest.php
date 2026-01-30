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

namespace Tests\Sylius\ImportExport\Functional\Exporting;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Sylius\ImportExport\Entity\ExportProcess;
use Sylius\ImportExport\Entity\ExportProcessInterface;
use Sylius\ImportExport\Messenger\Command\ExportCommand;
use Sylius\ImportExport\Messenger\Handler\ExportCommandHandler;
use Sylius\ImportExport\Serializer\DefaultSerializationGroups;
use Symfony\Component\Uid\Uuid;
use Tests\Sylius\ImportExport\Entity\Dummy;
use Tests\Sylius\ImportExport\Entity\DummyItem;
use Tests\Sylius\ImportExport\Functional\FunctionalTestCase;

final class ExportHandlerTest extends FunctionalTestCase
{
    private ExportCommandHandler $handler;

    private string $exportsDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->getContainer()->get('sylius_import_export.messenger.command_handler.export');
        $this->exportsDir = $this->getContainer()->getParameter('sylius_import_export.export_files_directory');

        $this->clearExportFiles();
    }

    protected function tearDown(): void
    {
        $this->clearExportFiles();

        parent::tearDown();
    }

    #[DataProvider('getDummyData')]
    #[Test]
    public function it_exports_data_to_json(array $dummiesData, string $serializationGroup, string $result): void
    {
        foreach ($dummiesData as $dummyData) {
            $dummy = $this->createDummy(...$dummyData);

            $this->entityManager->persist($dummy);
        }

        $this->entityManager->flush();

        $ids = array_values(array_column($dummiesData, 'uuid'));

        $processUid = (string) Uuid::v7();

        $this->createProcess(
            $processUid,
            'json',
            $ids,
            ['serialization_groups' => $serializationGroup],
        );

        $this->handler->__invoke(new ExportCommand($processUid, $ids, 0));

        $files = self::getExportFiles($this->exportsDir);

        $this->assertNotEmpty($files);

        $exportedFile = array_pop($files);

        $exportedData = (string) file_get_contents($exportedFile);

        $this->assertSame($result, $exportedData);
    }

    public static function getDummyData(): iterable
    {
        // Case 1: One basic dummy without items
        $dummiesData = [
            [
                'uuid' => 'uuid-1',
                'text' => 'Text A',
                'counter' => 1,
                'config' => ['enabled' => true],
                'dummyItems' => [],
            ],
        ];
        yield 'single basic dummy without items' => [
            'dummiesData' => $dummiesData,
            'serializationGroup' => DefaultSerializationGroups::EXPORT_GROUP,
            'result' => json_encode($dummiesData, \JSON_PRETTY_PRINT),
        ];

        yield 'single basic dummy without items and unknown serialization group' => [
            'dummiesData' => $dummiesData,
            'serializationGroup' => 'some-other-group',
            'result' => json_encode([[]], \JSON_PRETTY_PRINT),
        ];

        // Case 2: Two simple dummies
        $dummiesData = [
            [
                'uuid' => 'uuid-2',
                'text' => 'Text B1',
                'counter' => 2,
                'config' => ['enabled' => false],
                'dummyItems' => [
                    ['uuid' => 'item-uuid-1', 'text' => 'Item Text 1', 'counter' => 10, 'config' => ['flag' => true]],
                ],
            ],
            [
                'uuid' => 'uuid-3',
                'text' => 'Text B2',
                'counter' => 3,
                'config' => ['enabled' => true],
                'dummyItems' => [],
            ],
        ];
        yield 'collection of two simple dummies' => [
            'dummiesData' => $dummiesData,
            'serializationGroup' => DefaultSerializationGroups::EXPORT_GROUP,
            'result' => json_encode($dummiesData, \JSON_PRETTY_PRINT),
        ];

        // Case 3: Nested config, multiple items
        $dummiesData = [
            [
                'uuid' => 'uuid-4',
                'text' => 'Text C1',
                'counter' => 4,
                'config' => ['enabled' => true, 'mode' => 'full'],
                'dummyItems' => [
                    ['uuid' => 'item-uuid-2', 'text' => 'Item Text 2', 'counter' => 20, 'config' => ['flag' => false]],
                    ['uuid' => 'item-uuid-3', 'text' => 'Item Text 3', 'counter' => 30, 'config' => ['flag' => true]],
                ],
            ],
            [
                'uuid' => 'uuid-5',
                'text' => 'Text C2',
                'counter' => 5,
                'config' => ['enabled' => false, 'threshold' => 10],
                'dummyItems' => [],
            ],
        ];
        yield 'collection with nested config and multiple items' => [
            'dummiesData' => $dummiesData,
            'serializationGroup' => DefaultSerializationGroups::EXPORT_GROUP,
            'result' => json_encode($dummiesData, \JSON_PRETTY_PRINT),
        ];

        // Case 4: Deep nested config and mixed item complexity
        $dummiesData = [
            [
                'uuid' => 'uuid-6',
                'text' => 'Text D1',
                'counter' => 6,
                'config' => ['enabled' => true, 'settings' => ['theme' => 'dark']],
                'dummyItems' => [
                    ['uuid' => 'item-uuid-4', 'text' => 'Item Text 4', 'counter' => 40, 'config' => ['meta' => ['lang' => 'en']]],
                ],
            ],
            [
                'uuid' => 'uuid-7',
                'text' => 'Text D2',
                'counter' => 7,
                'config' => ['enabled' => false],
                'dummyItems' => [
                    ['uuid' => 'item-uuid-5', 'text' => 'Item Text 5', 'counter' => 50, 'config' => ['meta' => ['lang' => 'de']]],
                    ['uuid' => 'item-uuid-6', 'text' => 'Item Text 6', 'counter' => 60, 'config' => ['flag' => null]],
                ],
            ],
        ];
        yield 'collection with deep nested config and mixed item complexity' => [
            'dummiesData' => $dummiesData,
            'serializationGroup' => DefaultSerializationGroups::EXPORT_GROUP,
            'result' => json_encode($dummiesData, \JSON_PRETTY_PRINT),
        ];

        // Case 5: Edge values and deeply nested structure
        $dummiesData = [
            [
                'uuid' => 'uuid-8',
                'text' => '',
                'counter' => 0,
                'config' => ['enabled' => false, 'deep' => ['level1' => ['level2' => ['value' => 123]]]],
                'dummyItems' => [
                    ['uuid' => 'item-uuid-7', 'text' => '', 'counter' => 0, 'config' => ['tags' => ['x', 'y']]],
                ],
            ],
            [
                'uuid' => 'uuid-9',
                'text' => 'Final Dummy',
                'counter' => 999,
                'config' => ['enabled' => true, 'meta' => ['created' => '2025-01-01']],
                'dummyItems' => [],
            ],
        ];
        yield 'complex collection with edge values and deeply nested data' => [
            'dummiesData' => $dummiesData,
            'serializationGroup' => DefaultSerializationGroups::EXPORT_GROUP,
            'result' => json_encode($dummiesData, \JSON_PRETTY_PRINT),
        ];
    }

    protected static function getExportFiles(string $dir): array
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException('Directory ' . $dir . 'does not exist');
        }

        return array_map(
            fn (string $file) => $dir . '/' . $file,
            array_filter(
                scandir($dir) ?: [],
                static fn ($file) => str_ends_with($file, '.json'),
            ),
        );
    }

    private function clearExportFiles(): void
    {
        foreach (self::getExportFiles($this->exportsDir) as $file) {
            unlink($file);
        }
    }

    private function createDummy(
        string $uuid,
        string $text,
        int $counter,
        array $config,
        array $dummyItems,
    ): Dummy {
        $dummy = new Dummy();
        $dummy->setUuid($uuid);
        $dummy->setText($text);
        $dummy->setCounter($counter);
        $dummy->setConfig($config);

        foreach ($dummyItems as $dummyItem) {
            $dummy->addDummyItem($this->createDummyItem(...$dummyItem));
        }

        return $dummy;
    }

    private function createDummyItem(
        string $uuid,
        string $text,
        int $counter,
        array $config,
    ): DummyItem {
        $dummyItem = new DummyItem();
        $dummyItem->setUuid($uuid);
        $dummyItem->setText($text);
        $dummyItem->setCounter($counter);
        $dummyItem->setConfig($config);

        return $dummyItem;
    }

    private function createProcess(
        string $uuid,
        string $format,
        array $resourceIds,
        array $parameters,
    ): ExportProcessInterface {
        $process = new ExportProcess();
        $process->setUuid($uuid);
        $process->setBatchesCount(1);
        $process->setResource('sylius_import_export.test_dummy');
        $process->setFormat($format);
        $process->setStatus('processing');
        $process->setParameters(array_merge([
            'class' => Dummy::class,
        ], $parameters));
        $process->setResourceIds($resourceIds);

        $this->entityManager->persist($process);
        $this->entityManager->flush();

        return $process;
    }
}
