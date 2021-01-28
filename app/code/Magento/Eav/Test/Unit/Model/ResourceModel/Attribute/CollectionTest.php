<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model\ResourceModel\Attribute;

/**
 * Class CollectionTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Attribute\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eavConfigMock;

    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $entityTypeMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $select;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $selectRenderer;

    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->fetchStrategyMock = $this->createMock(
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class
        );
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        $this->eavConfigMock = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->entityTypeMock = $this->createPartialMock(\Magento\Eav\Model\Entity\Type::class, ['__wakeup']);
        $this->entityTypeMock->setAdditionalAttributeTable('some_extra_table');
        $this->eavConfigMock->expects($this->any())
            ->method('getEntityType')
            ->willReturn($this->entityTypeMock);

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturnSelf();

        $this->connectionMock = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['select', 'describeTable', 'quoteIdentifier', '_connect', '_quote']
        );
        $this->selectRenderer = $this->getMockBuilder(\Magento\Framework\DB\Select\SelectRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->select = new \Magento\Framework\DB\Select($this->connectionMock, $this->selectRenderer);

        $this->resourceMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['__wakeup', 'getConnection', 'getMainTable', 'getTable']
        );

        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->select);
        $this->connectionMock->expects($this->any())->method('quoteIdentifier')->willReturnArgument(0);
        $this->connectionMock->expects($this->any())
            ->method('describeTable')
            ->willReturnMap(
                [
                    [
                        'some_main_table',
                        null,
                        [
                            'col1' => [],
                            'col2' => [],
                        ],
                    ],
                    [
                        'some_extra_table',
                        null,
                        [
                            'col2' => [],
                            'col3' => [],
                        ]
                    ],
                    [
                        null,
                        null,
                        [
                            'col2' => [],
                            'col3' => [],
                            'col4' => [],
                        ]
                    ],
                ]
            );
        $this->connectionMock->expects($this->any())->method('_quote')->willReturnArgument(0);
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $this->resourceMock->expects($this->any())->method('getMainTable')->willReturn('some_main_table');
        $this->resourceMock->expects($this->any())->method('getTable')->willReturn('some_extra_table');
    }

    /**
     * @dataProvider initSelectDataProvider
     */
    public function testInitSelect($column, $value)
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\Customer\Model\ResourceModel\Attribute\Collection::class,
            [
                'entityFactory' => $this->entityFactoryMock,
                'logger' => $this->loggerMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'eventManager' => $this->eventManagerMock,
                'eavConfig' => $this->eavConfigMock,
                'storeManager' => $this->storeManagerMock,
                'connection' => $this->connectionMock,
                'resource' => $this->resourceMock
            ]
        );

        $this->model->addFieldToFilter($column, $value);
        $this->selectRenderer->expects($this->once())
            ->method('render')
            ->withAnyParameters();
        $this->model->getSelectCountSql()->assemble();
    }

    /**
     * @return array
     */
    public function initSelectDataProvider()
    {
        return [
            'main_table_expression' => [
                'col2', '1',
            ],
            'additional_table_expression' => [
                'col3', '2',
            ]
        ];
    }
}
