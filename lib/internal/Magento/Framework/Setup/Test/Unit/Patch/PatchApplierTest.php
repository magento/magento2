<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Test\Unit\Patch;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Module\ModuleResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\PatchBackwardCompatability;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Setup\Patch\PatchApplier;
use Magento\Framework\Setup\Patch\PatchFactory;
use Magento\Framework\Setup\Patch\PatchHistory;
use Magento\Framework\Setup\Patch\PatchReader;
use Magento\Framework\Setup\Patch\PatchRegistry;
use Magento\Framework\Setup\Patch\PatchRegistryFactory;

/**
 * Class PatchApplierTest
 * @package Magento\Framework\Setup\Test\Unit\Patch
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PatchApplierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PatchRegistryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $patchRegistryFactoryMock;

    /**
     * @var PatchReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPatchReaderMock;

    /**
     * @var PatchReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $schemaPatchReaderMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var ModuleResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleResourceMock;

    /**
     * @var PatchHistory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $patchHistoryMock;

    /**
     * @var PatchFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $patchFactoryMock;

    /**
     * @var \Magento\Framework\Setup\SetupInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $schemaSetupMock;

    /**
     * @var ModuleDataSetupInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleDataSetupMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var PatchApplier
     */
    private $patchApllier;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var PatchBackwardCompatability |\PHPUnit_Framework_MockObject_MockObject
     */
    private $patchBackwardCompatability;

    protected function setUp()
    {
        $this->patchRegistryFactoryMock = $this->createMock(PatchRegistryFactory::class);
        $this->dataPatchReaderMock = $this->createMock(PatchReader::class);
        $this->schemaPatchReaderMock = $this->createMock(PatchReader::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->moduleResourceMock = $this->createMock(ModuleResource::class);
        $this->patchHistoryMock = $this->createMock(PatchHistory::class);
        $this->patchFactoryMock = $this->createMock(PatchFactory::class);
        $this->schemaSetupMock = $this->createMock(SchemaSetupInterface::class);
        $this->moduleDataSetupMock = $this->createMock(ModuleDataSetupInterface::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->moduleDataSetupMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);

        $objectManager = new ObjectManager($this);
        $this->patchBackwardCompatability = $objectManager->getObject(
            PatchBackwardCompatability::class,
            [
                'moduleResource' => $this->moduleResourceMock
            ]
        );
        $this->patchApllier = $objectManager->getObject(
            PatchApplier::class,
            [
                'patchRegistryFactory' => $this->patchRegistryFactoryMock,
                'dataPatchReader' => $this->dataPatchReaderMock,
                'schemaPatchReader' => $this->schemaPatchReaderMock,
                'resourceConnection' => $this->resourceConnectionMock,
                'moduleResource' => $this->moduleResourceMock,
                'patchHistory' => $this->patchHistoryMock,
                'patchFactory' => $this->patchFactoryMock,
                'objectManager' => $this->objectManagerMock,
                'schemaSetup' => $this->schemaSetupMock,
                'moduleDataSetup' => $this->moduleDataSetupMock,
                'patchBackwardCompatability' => $this->patchBackwardCompatability
            ]
        );
        require_once __DIR__ . '/../_files/data_patch_classes.php';
        require_once __DIR__ . '/../_files/schema_patch_classes.php';
    }

    /**
     * @param $moduleName
     * @param $dataPatches
     * @param $moduleVersionInDb
     *
     * @dataProvider applyDataPatchDataNewModuleProvider()
     */
    public function testApplyDataPatchForNewlyInstalledModule($moduleName, $dataPatches, $moduleVersionInDb)
    {
        $this->dataPatchReaderMock->expects($this->once())
            ->method('read')
            ->with($moduleName)
            ->willReturn($dataPatches);

        $this->moduleResourceMock->expects($this->any())->method('getDataVersion')->willReturnMap(
            [
                [$moduleName, $moduleVersionInDb]
            ]
        );

        $patches = [
            \SomeDataPatch::class,
            \OtherDataPatch::class
        ];
        $patchRegistryMock = $this->createAggregateIteratorMock(PatchRegistry::class, $patches, ['registerPatch']);
        $patchRegistryMock->expects($this->exactly(2))
            ->method('registerPatch');

        $this->patchRegistryFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($patchRegistryMock);

        $patch1 = $this->createMock(\SomeDataPatch::class);
        $patch1->expects($this->once())->method('apply');
        $patch2 = $this->createMock(\OtherDataPatch::class);
        $patch2->expects($this->once())->method('apply');
        $this->objectManagerMock->expects($this->any())->method('create')->willReturnMap(
            [
                ['\\' . \SomeDataPatch::class, ['moduleDataSetup' => $this->moduleDataSetupMock], $patch1],
                ['\\' . \OtherDataPatch::class, ['moduleDataSetup' => $this->moduleDataSetupMock], $patch2],
            ]
        );
        $this->connectionMock->expects($this->exactly(2))->method('beginTransaction');
        $this->connectionMock->expects($this->exactly(2))->method('commit');
        $this->patchHistoryMock->expects($this->any())->method('fixPatch')->willReturnMap(
            [
                [get_class($patch1)],
                [get_class($patch2)],
            ]
        );
        $this->patchApllier->applyDataPatch($moduleName);
    }

    /**
     * @return array
     */
    public function applyDataPatchDataNewModuleProvider()
    {
        return [
            'newly installed module' => [
                'moduleName' => 'Module1',
                'dataPatches' => [
                    \SomeDataPatch::class,
                    \OtherDataPatch::class
                ],
                'moduleVersionInDb' => null,
            ],
        ];
    }

    /**
     * @param $moduleName
     * @param $dataPatches
     * @param $moduleVersionInDb
     *
     * @dataProvider applyDataPatchDataInstalledModuleProvider()
     */
    public function testApplyDataPatchForInstalledModule($moduleName, $dataPatches, $moduleVersionInDb)
    {
        $this->dataPatchReaderMock->expects($this->once())
            ->method('read')
            ->with($moduleName)
            ->willReturn($dataPatches);

        $this->moduleResourceMock->expects($this->any())->method('getDataVersion')->willReturnMap(
            [
                [$moduleName, $moduleVersionInDb]
            ]
        );

        $patches = [
            \SomeDataPatch::class,
            \OtherDataPatch::class
        ];
        $patchRegistryMock = $this->createAggregateIteratorMock(
            PatchRegistry::class,
            $patches,
            ['registerPatch']
        );
        $patchRegistryMock->expects(self::exactly(2))
            ->method('registerPatch');

        $this->patchRegistryFactoryMock->expects(self::any())
            ->method('create')
            ->willReturn($patchRegistryMock);

        $patch1 = $this->createMock(\SomeDataPatch::class);
        $patch1->expects(self::never())->method('apply');
        $patch2 = $this->createMock(\OtherDataPatch::class);
        $patch2->expects(self::once())->method('apply');
        $this->objectManagerMock->expects(self::any())->method('create')->willReturnMap(
            [
                ['\\' . \SomeDataPatch::class, ['moduleDataSetup' => $this->moduleDataSetupMock], $patch1],
                ['\\' . \OtherDataPatch::class, ['moduleDataSetup' => $this->moduleDataSetupMock], $patch2],
            ]
        );
        $this->connectionMock->expects(self::exactly(1))->method('beginTransaction');
        $this->connectionMock->expects(self::exactly(1))->method('commit');
        $this->patchHistoryMock->expects(self::exactly(2))->method('fixPatch');
        $this->patchApllier->applyDataPatch($moduleName);
    }

    /**
     * @return array
     */
    public function applyDataPatchDataInstalledModuleProvider()
    {
        return [
            'upgrade module iwth only OtherDataPatch' => [
                'moduleName' => 'Module1',
                'dataPatches' => [
                    \SomeDataPatch::class,
                    \OtherDataPatch::class
                ],
                'moduleVersionInDb' => '2.0.0',
            ]
        ];
    }

    /**
     * @param $moduleName
     * @param $dataPatches
     * @param $moduleVersionInDb
     *
     * @expectedException \Magento\Framework\Setup\Exception
     * @expectedExceptionMessage Patch Apply Error
     *
     * @dataProvider applyDataPatchDataInstalledModuleProvider()
     */
    public function testApplyDataPatchRollback($moduleName, $dataPatches, $moduleVersionInDb)
    {
        $this->dataPatchReaderMock->expects($this->once())
            ->method('read')
            ->with($moduleName)
            ->willReturn($dataPatches);

        $this->moduleResourceMock->expects($this->any())->method('getDataVersion')->willReturnMap(
            [
                [$moduleName, $moduleVersionInDb]
            ]
        );

        $patches = [
            \SomeDataPatch::class,
            \OtherDataPatch::class
        ];
        $patchRegistryMock = $this->createAggregateIteratorMock(PatchRegistry::class, $patches, ['registerPatch']);
        $patchRegistryMock->expects($this->exactly(2))
            ->method('registerPatch');

        $this->patchRegistryFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($patchRegistryMock);

        $patch1 = $this->createMock(\SomeDataPatch::class);
        $patch1->expects($this->never())->method('apply');
        $patch2 = $this->createMock(\OtherDataPatch::class);
        $exception = new \Exception('Patch Apply Error');
        $patch2->expects($this->once())->method('apply')->willThrowException($exception);
        $this->objectManagerMock->expects($this->any())->method('create')->willReturnMap(
            [
                ['\\' . \SomeDataPatch::class, ['moduleDataSetup' => $this->moduleDataSetupMock], $patch1],
                ['\\' . \OtherDataPatch::class, ['moduleDataSetup' => $this->moduleDataSetupMock], $patch2],
            ]
        );
        $this->connectionMock->expects($this->exactly(1))->method('beginTransaction');
        $this->connectionMock->expects($this->never())->method('commit');
        $this->connectionMock->expects($this->exactly(1))->method('rollback');
        $this->patchHistoryMock->expects($this->exactly(1))->method('fixPatch');
        $this->patchApllier->applyDataPatch($moduleName);
    }

    /**
     * @expectedException \Magento\Framework\Setup\Exception
     * @expectedExceptionMessageRegExp "Patch [a-zA-Z0-9\_]+ should implement DataPatchInterface"
     */
    public function testNonDataPatchApply()
    {
        $this->dataPatchReaderMock->expects($this->once())
            ->method('read')
            ->with('module1')
            ->willReturn([\stdClass::class]);
        $patchRegistryMock = $this->createAggregateIteratorMock(
            PatchRegistry::class,
            [\stdClass::class],
            ['registerPatch']
        );
        $patchRegistryMock->expects($this->exactly(1))
            ->method('registerPatch');

        $this->patchRegistryFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($patchRegistryMock);

        $this->objectManagerMock->expects($this->any())->method('create')->willReturnMap(
            [
                [
                    '\\' . \stdClass::class,
                    ['moduleDataSetup' => $this->moduleDataSetupMock],
                    $this->createMock(\stdClass::class)
                ],
            ]
        );

        $this->patchApllier->applyDataPatch('module1');
    }

    public function testNonTransactionablePatch()
    {
        $patches = [\NonTransactionableDataPatch::class];
        $this->dataPatchReaderMock->expects($this->once())
            ->method('read')
            ->with('module1')
            ->willReturn($patches);
        $patchRegistryMock = $this->createAggregateIteratorMock(
            PatchRegistry::class,
            $patches,
            ['registerPatch']
        );
        $patchRegistryMock->expects($this->exactly(1))
            ->method('registerPatch');

        $this->patchRegistryFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($patchRegistryMock);

        $patch1 = $this->createMock($patches[0]);
        $patch1->expects($this->exactly(1))->method('apply');
        $this->connectionMock->expects($this->never())->method('beginTransaction');
        $this->connectionMock->expects($this->never())->method('commit');
        $this->connectionMock->expects($this->never())->method('rollback');
        $this->patchHistoryMock->expects($this->once())->method('fixPatch')->with(get_class($patch1));
        $this->objectManagerMock->expects($this->any())->method('create')->willReturnMap(
            [
                [
                    '\\' . $patches[0],
                    ['moduleDataSetup' => $this->moduleDataSetupMock],
                    $patch1
                ],
            ]
        );

        $this->patchApllier->applyDataPatch('module1');
    }

    /**
     * @param $moduleName
     * @param $schemaPatches
     * @param $moduleVersionInDb
     *
     * @dataProvider schemaPatchDataProvider()
     */
    public function testSchemaPatchAplly($moduleName, $schemaPatches, $moduleVersionInDb)
    {
        $this->schemaPatchReaderMock->expects($this->once())
            ->method('read')
            ->with($moduleName)
            ->willReturn($schemaPatches);

        $this->moduleResourceMock->expects($this->any())->method('getDbVersion')->willReturnMap(
            [
                [$moduleName, $moduleVersionInDb]
            ]
        );

        $patches = [
            \SomeSchemaPatch::class,
            \OtherSchemaPatch::class
        ];
        $patchRegistryMock = $this->createAggregateIteratorMock(PatchRegistry::class, $patches, ['registerPatch']);
        $patchRegistryMock->expects($this->exactly(2))
            ->method('registerPatch');

        $this->patchRegistryFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($patchRegistryMock);

        $patch1 = $this->createMock(\SomeSchemaPatch::class);
        $patch1->expects($this->never())->method('apply');
        $patch2 = $this->createMock(\OtherSchemaPatch::class);
        $patch2->expects($this->once())->method('apply');
        $this->patchFactoryMock->expects($this->any())->method('create')->willReturnMap(
            [
                [\SomeSchemaPatch::class, ['schemaSetup' => $this->schemaSetupMock], $patch1],
                [\OtherSchemaPatch::class, ['schemaSetup' => $this->schemaSetupMock], $patch2],
            ]
        );
        $this->connectionMock->expects($this->never())->method('beginTransaction');
        $this->connectionMock->expects($this->never())->method('commit');
        $this->patchHistoryMock->expects($this->exactly(2))->method('fixPatch');
        $this->patchApllier->applySchemaPatch($moduleName);
    }

    public function testRevertDataPatches()
    {
        $patches = [\RevertableDataPatch::class];
        $this->dataPatchReaderMock->expects($this->once())
            ->method('read')
            ->with('module1')
            ->willReturn($patches);
        $patchRegistryMock = $this->createAggregateIteratorMock(
            PatchRegistry::class,
            $patches,
            ['registerPatch', 'getReverseIterator']
        );
        $patchRegistryMock->expects($this->exactly(1))
            ->method('registerPatch');
        $patchRegistryMock->expects($this->once())->method('getReverseIterator')
            ->willReturn(array_reverse($patches));

        $this->patchRegistryFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($patchRegistryMock);

        $patch1 = $this->createMock($patches[0]);
        $patch1->expects($this->exactly(1))->method('revert');
        $this->connectionMock->expects($this->once())->method('beginTransaction');
        $this->connectionMock->expects($this->once())->method('commit');
        $this->connectionMock->expects($this->never())->method('rollback');
        $this->patchHistoryMock->expects($this->once())->method('revertPatchFromHistory')->with(get_class($patch1));
        $this->objectManagerMock->expects($this->any())->method('create')->willReturnMap(
            [
                [
                    '\\' . $patches[0],
                    ['moduleDataSetup' => $this->moduleDataSetupMock],
                    $patch1
                ],
            ]
        );

        $this->patchApllier->revertDataPatches('module1');
    }

    /**
     * @return array
     */
    public function schemaPatchDataProvider()
    {
        return [
            'upgrade module iwth only OtherSchemaPatch' => [
                'moduleName' => 'Module1',
                'schemaPatches' => [
                    \SomeSchemaPatch::class,
                    \OtherSchemaPatch::class
                ],
                'moduleVersionInDb' => '2.0.0',
            ]
        ];
    }
    /**
     * Create mock of class that implements IteratorAggregate
     *
     * @param string $className
     * @param array $items
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject|\IteratorAggregate
     * @throws \Exception
     */
    private function createAggregateIteratorMock($className, array $items = [], array $methods = [])
    {
        if (!in_array(ltrim(\IteratorAggregate::class, '\\'), class_implements($className))) {
            throw new \Exception('Mock possible only for classes that implement IteratorAggregate interface.');
        }
        /**
         * PHPUnit_Framework_MockObject_MockObject
         */
        $someIterator = $this->createMock(\ArrayIterator::class);

        $mockIteratorAggregate = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods(array_merge($methods, ['getIterator']))
            ->getMock();

        $mockIteratorAggregate->expects($this->any())->method('getIterator')->willReturn($someIterator);

        $iterator = new \ArrayIterator($items);

        $someIterator->expects($this->any())
            ->method('rewind')
            ->willReturnCallback(function () use ($iterator) {
                $iterator->rewind();
            });

        $someIterator->expects($this->any())
            ->method('current')
            ->willReturnCallback(function () use ($iterator) {
                return $iterator->current();
            });

        $someIterator->expects($this->any())
            ->method('key')
            ->willReturnCallback(function () use ($iterator) {
                return $iterator->key();
            });

        $someIterator->expects($this->any())
            ->method('next')
            ->willReturnCallback(function () use ($iterator) {
                $iterator->next();
            });

        $someIterator->expects($this->any())
            ->method('valid')
            ->willReturnCallback(function () use ($iterator) {
                return $iterator->valid();
            });

        return $mockIteratorAggregate;
    }
}
