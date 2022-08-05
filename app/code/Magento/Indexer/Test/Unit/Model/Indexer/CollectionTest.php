<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Indexer;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Indexer\Model\Indexer\Collection;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\ResourceModel\Indexer\State\Collection as StateCollection;
use Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $statesFactoryMock;

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

        $this->statesFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityFactoryMock = $this->getMockBuilder(EntityFactoryInterface::class)
            ->getMock();

        $this->collection = $this->objectManagerHelper->getObject(
            Collection::class,
            [
                'entityFactory' => $this->entityFactoryMock,
                'config' => $this->configMock,
                'statesFactory' => $this->statesFactoryMock,
            ]
        );
    }

    /**
     * @param array $indexersData
     * @param array $states
     * @dataProvider loadDataDataProvider
     */
    public function testLoadData(array $indexersData, array $states)
    {
        $statesCollection = $this->getMockBuilder(StateCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->statesFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($statesCollection);
        $statesCollection->method('getItems')
            ->willReturn($states);

        $calls = [];
        foreach ($indexersData as $indexerId => $indexerData) {
            $indexer = $this->getIndexerMock($indexerData);
            $state = $states[$indexerId] ?? '';
            $indexer
                ->expects($this->once())
                ->method('load')
                ->with($indexerId);
            $indexer
                ->expects($this->exactly($state ? 1 : 0))
                ->method('setState')
                ->with($state);
            $calls[] = $indexer;
        }
        $this->configMock
            ->method('getIndexers')
            ->willReturn($indexersData);
        $this->entityFactoryMock
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$calls);

        $this->assertFalse((bool)$this->collection->isLoaded());
        $this->assertInstanceOf(Collection::class, $this->collection->loadData());
        $itemIds = [];
        foreach ($this->collection->getItems() as $item) {
            $itemIds[] = $item->getId();
        }
        $this->assertEmpty(array_diff($itemIds, array_keys($indexersData)));
        $this->assertTrue($this->collection->isLoaded());
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
                        'indexer_id' => 'indexer_2',
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                    ],
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                    ],
                ],
                'states' => [
                    'indexer_2' => $this->getStateMock(['indexer_id' => 'indexer_2']),
                    'indexer_3' => $this->getStateMock(['indexer_id' => 'indexer_3']),
                ],
            ]
        ];
    }

    /**
     * @param array $indexersData
     * @dataProvider getAllIdsDataProvider
     */
    public function testGetAllIds(array $indexersData)
    {
        $statesCollection = $this->getMockBuilder(StateCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->statesFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($statesCollection);
        $statesCollection->method('getItems')
            ->willReturn([]);

        $calls = [];
        foreach ($indexersData as $indexerData) {
            $calls[] = $this->getIndexerMock($indexerData);
        }
        $this->configMock
            ->method('getIndexers')
            ->willReturn($indexersData);
        $this->entityFactoryMock
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$calls);

        $this->assertEmpty(array_diff($this->collection->getAllIds(), array_keys($indexersData)));
    }

    /**
     * @return array
     */
    public function getAllIdsDataProvider()
    {
        return [
            [
                'indexers' => [
                    'indexer_2' => [
                        'indexer_id' => 'indexer_2',
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                    ],
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                    ],
                ],
            ]
        ];
    }

    /**
     * @param string $methodName
     * @param array $arguments
     * @dataProvider stubMethodsDataProvider
     */
    public function testStubMethods(string $methodName, array $arguments)
    {
        $this->statesFactoryMock
            ->expects($this->never())
            ->method('create');
        $collection = $this->objectManagerHelper->getObject(
            Collection::class,
            [
                'entityFactory' => $this->entityFactoryMock,
                'config' => $this->configMock,
                'statesFactory' => $this->statesFactoryMock,
                '_items' => [$this->getIndexerMock()],
            ]
        );
        $this->assertEmpty($collection->{$methodName}(...$arguments));
    }

    /**
     * @return array
     */
    public function stubMethodsDataProvider()
    {
        return [
            [
                'getColumnValues',
                ['colName'],
            ],
            [
                'getItemsByColumnValue',
                ['colName', 'value']
            ],
            [
                'getItemByColumnValue',
                ['colName', 'value']
            ],
            [
                'toXml',
                []
            ],
            [
                'toArray',
                []
            ],
            [
                'toOptionArray',
                []
            ],
            [
                'toOptionHash',
                []
            ],
        ];
    }

    /**
     * @param string $methodName
     * @param array $arguments
     * @dataProvider stubMethodsWithReturnSelfDataProvider
     */
    public function testStubMethodsWithReturnSelf(string $methodName, array $arguments)
    {
        $this->statesFactoryMock
            ->expects($this->never())
            ->method('create');
        $collection = $this->objectManagerHelper->getObject(
            Collection::class,
            [
                'entityFactory' => $this->entityFactoryMock,
                'config' => $this->configMock,
                'statesFactory' => $this->statesFactoryMock,
                '_items' => [$this->getIndexerMock()],
            ]
        );
        $this->assertInstanceOf(Collection::class, $collection->{$methodName}(...$arguments));
    }

    /**
     * @return array
     */
    public function stubMethodsWithReturnSelfDataProvider()
    {
        return [
            [
                'setDataToAll',
                ['colName', 'value']
            ],
            [
                'setItemObjectClass',
                ['notValidClassName']
            ],
        ];
    }

    /**
     * @return MockObject|IndexerInterface
     */
    private function getIndexerMock(array $data = [])
    {
        /** @var MockObject|IndexerInterface $indexer */
        $indexer = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();
        if (isset($data['indexer_id'])) {
            $indexer->method('getId')
                ->willReturn($data['indexer_id']);
        }
        return $indexer;
    }

    /**
     * @param array $data
     * @return MockObject|State
     */
    private function getStateMock(array $data = [])
    {
        /** @var MockObject|State $state */
        $state = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        if (isset($data['indexer_id'])) {
            $state->method('getIndexerId')
                ->willReturn($data['indexer_id']);
        }

        return $state;
    }
}
