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

namespace Tests\Sylius\ImportExport\Functional\Importing;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Sylius\ImportExport\Entity\ImportProcess;
use Sylius\ImportExport\Entity\ImportProcessInterface;
use Sylius\ImportExport\Messenger\Command\ImportCommand;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use Tests\Sylius\ImportExport\Entity\Dummy;
use Tests\Sylius\ImportExport\Functional\FunctionalTestCase;

final class ImportHandlerTest extends FunctionalTestCase
{
    private MessageBusInterface $commandBus;

    private string $importsDir;

    private RepositoryInterface $processRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = $this->getContainer()->get('sylius_import_export.import.command_bus');
        $this->importsDir = $this->getContainer()->getParameter('sylius_import_export.import_files_directory');
        $this->processRepository = $this->getContainer()->get('sylius_import_export.repository.process_import');

        $this->clearImportFiles();
    }

    protected function tearDown(): void
    {
        $this->clearImportFiles();

        parent::tearDown();
    }

    #[DataProvider('getImportData')]
    #[Test]
    public function it_imports_data_from_array(array $importData, int $expectedCount): void
    {
        $processUuid = (string) Uuid::v7();

        $process = $this->createImportProcess(
            $processUuid,
            'json',
            '/tmp/test-import.json',
            [],
        );

        $this->commandBus->dispatch(new ImportCommand($processUuid, $importData));

        $dummyRepository = $this->entityManager->getRepository(Dummy::class);
        $importedDummies = $dummyRepository->findAll();

        $this->assertCount($expectedCount, $importedDummies);

        $this->entityManager->refresh($process);
        $this->assertSame($expectedCount, $process->getImportedCount());
        $this->assertSame('success', $process->getStatus());
    }

    #[Test]
    public function it_imports_single_dummy_with_basic_data(): void
    {
        $importData = [
            [
                'uuid' => 'test-uuid-1',
                'text' => 'Test Text',
                'counter' => 42,
                'config' => ['enabled' => true],
            ],
        ];

        $processUuid = (string) Uuid::v7();
        $process = $this->createImportProcess($processUuid, 'json', '/tmp/test.json', []);

        $this->commandBus->dispatch(new ImportCommand($processUuid, $importData));

        $dummyRepository = $this->entityManager->getRepository(Dummy::class);
        $dummy = $dummyRepository->findOneBy(['uuid' => 'test-uuid-1']);

        $this->assertNotNull($dummy);
        $this->assertSame('Test Text', $dummy->getText());
        $this->assertSame(42, $dummy->getCounter());
        $this->assertSame(['enabled' => true], $dummy->getConfig());
    }

    #[Test]
    public function it_imports_dummy_with_nested_config(): void
    {
        $importData = [
            [
                'uuid' => 'test-uuid-2',
                'text' => 'Complex Test',
                'counter' => 100,
                'config' => [
                    'enabled' => true,
                    'settings' => [
                        'theme' => 'dark',
                        'notifications' => ['email', 'sms'],
                    ],
                ],
            ],
        ];

        $processUuid = (string) Uuid::v7();
        $process = $this->createImportProcess($processUuid, 'json', '/tmp/test.json', []);

        $this->commandBus->dispatch(new ImportCommand($processUuid, $importData));

        $dummyRepository = $this->entityManager->getRepository(Dummy::class);
        $dummy = $dummyRepository->findOneBy(['uuid' => 'test-uuid-2']);

        $this->assertNotNull($dummy);
        $this->assertSame('Complex Test', $dummy->getText());
        $this->assertSame(100, $dummy->getCounter());

        $expectedConfig = [
            'enabled' => true,
            'settings' => [
                'theme' => 'dark',
                'notifications' => ['email', 'sms'],
            ],
        ];
        $this->assertSame($expectedConfig, $dummy->getConfig());
    }

    #[Test]
    public function it_handles_multiple_batches_correctly(): void
    {
        $processUuid = (string) Uuid::v7();
        $process = $this->createImportProcess($processUuid, 'json', '/tmp/test.json', []);
        $process->setBatchesCount(3); // Simulate 3 batches total

        $batchData1 = [
            ['uuid' => 'batch1-1', 'text' => 'Batch 1 Item 1', 'counter' => 1, 'config' => []],
            ['uuid' => 'batch1-2', 'text' => 'Batch 1 Item 2', 'counter' => 2, 'config' => []],
        ];
        $this->commandBus->dispatch(new ImportCommand($processUuid, $batchData1));

        $this->entityManager->refresh($process);
        $this->assertSame(2, $process->getImportedCount());
        $this->assertSame(2, $process->getBatchesCount());
        $this->assertSame('processing', $process->getStatus());

        $batchData2 = [
            ['uuid' => 'batch2-1', 'text' => 'Batch 2 Item 1', 'counter' => 3, 'config' => []],
        ];
        $this->commandBus->dispatch(new ImportCommand($processUuid, $batchData2));

        $this->entityManager->refresh($process);
        $this->assertSame(3, $process->getImportedCount());
        $this->assertSame(1, $process->getBatchesCount());
        $this->assertSame('processing', $process->getStatus());

        $batchData3 = [
            ['uuid' => 'batch3-1', 'text' => 'Batch 3 Item 1', 'counter' => 4, 'config' => []],
        ];
        $this->commandBus->dispatch(new ImportCommand($processUuid, $batchData3));

        $this->entityManager->refresh($process);
        $this->assertSame(4, $process->getImportedCount());
        $this->assertSame(0, $process->getBatchesCount());
        $this->assertSame('success', $process->getStatus());

        $dummyRepository = $this->entityManager->getRepository(Dummy::class);
        $allDummies = $dummyRepository->findAll();
        $this->assertCount(4, $allDummies);
    }

    #[Test]
    public function it_handles_validation_errors(): void
    {
        $processUuid = (string) Uuid::v7();
        $process = $this->createImportProcess(
            $processUuid,
            'json',
            '/tmp/test.json',
            ['validation_groups' => ['Default']],
        );

        $invalidData = [
            [
                'uuid' => 'invalid-uuid',
                'text' => '',
                'counter' => -1,
                'config' => [],
            ],
        ];

        $this->commandBus->dispatch(new ImportCommand($processUuid, $invalidData));

        $processRepository = $this->getContainer()->get('sylius_import_export.repository.process_import');
        $process = $processRepository->find($processUuid);
        $this->assertSame('failed', $process->getStatus());
        $this->assertNotNull($process->getErrorMessage());
        $this->assertStringContainsString('Validation failed', $process->getErrorMessage());
    }

    #[Test]
    public function it_uses_custom_validation_groups(): void
    {
        $processUuid = (string) Uuid::v7();
        $process = $this->createImportProcess(
            $processUuid,
            'json',
            '/tmp/test.json',
            ['validation_groups' => ['import']],
        );

        $importData = [
            [
                'uuid' => 'custom-validation-uuid',
                'text' => 'Custom Validation Test',
                'counter' => 50,
                'config' => ['validated' => true],
            ],
        ];

        $this->commandBus->dispatch(new ImportCommand($processUuid, $importData));

        $this->entityManager->refresh($process);
        $this->assertSame(1, $process->getImportedCount());
        $this->assertSame('success', $process->getStatus());
    }

    #[Test]
    public function it_handles_process_not_found_error(): void
    {
        $this->expectException(HandlerFailedException::class);
        $this->expectExceptionMessage('Process with uuid "non-existent-uuid" not found.');

        $this->commandBus->dispatch(new ImportCommand('non-existent-uuid', []));
    }

    public static function getImportData(): array
    {
        return [
            'single basic dummy' => [
                [
                    [
                        'uuid' => 'basic-uuid-1',
                        'text' => 'Basic Text',
                        'counter' => 10,
                        'config' => ['enabled' => false],
                    ],
                ],
                1,
            ],
            'multiple basic dummies' => [
                [
                    [
                        'uuid' => 'multi-uuid-1',
                        'text' => 'Multi Text 1',
                        'counter' => 20,
                        'config' => ['priority' => 'high'],
                    ],
                    [
                        'uuid' => 'multi-uuid-2',
                        'text' => 'Multi Text 2',
                        'counter' => 30,
                        'config' => ['priority' => 'low'],
                    ],
                ],
                2,
            ],
            'complex nested config dummies' => [
                [
                    [
                        'uuid' => 'complex-uuid-1',
                        'text' => 'Complex Text 1',
                        'counter' => 100,
                        'config' => [
                            'enabled' => true,
                            'metadata' => [
                                'tags' => ['important', 'urgent'],
                                'created_by' => 'system',
                            ],
                        ],
                    ],
                    [
                        'uuid' => 'complex-uuid-2',
                        'text' => 'Complex Text 2',
                        'counter' => 200,
                        'config' => [
                            'enabled' => false,
                            'settings' => [
                                'auto_process' => true,
                                'retry_count' => 3,
                            ],
                        ],
                    ],
                ],
                2,
            ],
            'edge case values' => [
                [
                    [
                        'uuid' => 'edge-uuid-1',
                        'text' => 'Valid text',
                        'counter' => 0,
                        'config' => [],
                    ],
                    [
                        'uuid' => 'edge-uuid-2',
                        'text' => 'Simple edge case text',
                        'counter' => 999999,
                        'config' => [
                            'special_chars' => '!@#$%^&*()_+-={}|[]\\:";\'<>?,./',
                        ],
                    ],
                ],
                2,
            ],
        ];
    }

    private function clearImportFiles(): void
    {
        if (!is_dir($this->importsDir)) {
            return;
        }

        $files = glob($this->importsDir . '/*');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    private function createImportProcess(
        string $uuid,
        string $format,
        string $filePath,
        array $parameters,
    ): ImportProcessInterface {
        $process = new ImportProcess();
        $process->setUuid($uuid);
        $process->setBatchesCount(1);
        $process->setResource('sylius_import_export.test_dummy');
        $process->setFormat($format);
        $process->setFilePath($filePath);
        $process->setStatus('processing');
        $process->setParameters($parameters);

        $this->entityManager->persist($process);
        $this->entityManager->flush();

        return $process;
    }
}
