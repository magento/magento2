<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Ui\DataProvider\Indexer;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Indexer\Ui\DataProvider\Indexer\DataCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataCollectionTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var DataCollection
     */
    private $dataCollection;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var EntityFactoryInterface|MockObject
     */
    private $entityFactoryMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();

        $this->indexerRegistryMock = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityFactoryMock = $this->getMockBuilder(EntityFactoryInterface::class)
            ->getMock();

        $this->dataCollection = $this->objectManagerHelper->getObject(
            DataCollection::class,
            [
                'entityFactory' => $this->entityFactoryMock,
                'config' => $this->configMock,
                'indexerRegistry' => $this->indexerRegistryMock,
            ]
        );
    }

    /**
     * @param array $indexersData
     * @dataProvider loadDataDataProvider
     */
    public function testLoadData(array $indexersData)
    {
        $calls = [];
        foreach ($indexersData as $indexerId => $data) {
            $indexer = $this->getIndexerMock($data);
            $calls[] = [$indexerId, $indexer];
        }
        $this->configMock
            ->method('getIndexers')
            ->willReturn($indexersData);
        $this->entityFactoryMock
            ->method('create')
            ->willReturnMap([[DataObject::class, [], new DataObject()]]);
        $this->indexerRegistryMock
            ->expects($this->exactly(count($indexersData)))
            ->method('get')
            ->willReturnMap($calls);
        $this->assertFalse((bool)$this->dataCollection->isLoaded());
        foreach ($this->dataCollection->getItems() as $item) {
            $this->assertEmpty(
                array_diff(
                    [
                        'indexer_id',
                        'title',
                        'description',
                        'is_scheduled',
                        'status',
                        'updated',
                    ],
                    array_keys($item->getData())
                )
            );
            $this->assertEmpty(
                array_diff(
                    $indexersData[$item->getData('indexer_id')],
                    $item->getData()
                )
            );
        }
        $this->assertTrue($this->dataCollection->isLoaded());
    }

    /**
     * @return array
     */
    public function loadDataDataProvider()
    {
        return [
            [
                'indexers' => [
                    'indexer_2' => [
                        'getId' => 'indexer_2',
                        'getTitle' => 'Title_2',
                        'getDescription' => 'Description_2',
                        'isScheduled' => true,
                        'getStatus' => StateInterface::STATUS_INVALID,
                        'getLatestUpdated' => '2017/07/01'
                    ],
                    'indexer_3' => [
                        'getId' => 'indexer_3',
                        'getTitle' => 'Title_3',
                        'getDescription' => 'Description_3',
                        'isScheduled' => true,
                        'getStatus' => StateInterface::STATUS_VALID,
                        'getLatestUpdated' => '2017/07/02'
                    ],
                    'indexer_1' => [
                        'getId' => 'indexer_1',
                        'getTitle' => 'Title_1',
                        'getDescription' => 'Description_1',
                        'isScheduled' => false,
                        'getStatus' => StateInterface::STATUS_INVALID,
                        'getLatestUpdated' => '2017/07/03'
                    ],
                ],
            ]
        ];
    }

    /**
     * @param array $data
     * @return MockObject|IndexerInterface
     */
    private function getIndexerMock(array $data = [])
    {
        /** @var MockObject|IndexerInterface $indexer */
        $indexer = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();
        foreach ($data as $methodName => $result) {
            $indexer
                ->method($methodName)
                ->willReturn($result);
        }
        return $indexer;
    }
}
