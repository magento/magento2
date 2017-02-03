<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Eav\Test\Unit\Model\ResourceModel\Attribute;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Attribute\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $entityTypeMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false);
        $this->loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $this->fetchStrategyMock = $this->getMock('Magento\Framework\Data\Collection\Db\FetchStrategyInterface');
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');

        $this->eavConfigMock = $this->getMock('Magento\Eav\Model\Config', [], [], '', false);
        $this->entityTypeMock = $this->getMock('Magento\Eav\Model\Entity\Type', ['__wakeup'], [], '', false);
        $this->entityTypeMock->setAdditionalAttributeTable('some_extra_table');
        $this->eavConfigMock->expects($this->any())
            ->method('getEntityType')
            ->will($this->returnValue($this->entityTypeMock));

        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnSelf());

        $this->connectionMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['select', 'describeTable', 'quoteIdentifier', '_connect', '_quote'],
            [],
            '',
            false);

        $this->select = new \Magento\Framework\DB\Select($this->connectionMock);

        $this->resourceMock = $this->getMockForAbstractClass(
            'Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            [],
            '',
            false,
            true,
            true,
            ['__wakeup', 'getConnection', 'getMainTable', 'getTable']
        );

        $this->connectionMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->select));
        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->will($this->returnArgument(0));
        $this->connectionMock->expects($this->any())
            ->method('describeTable')
            ->will($this->returnvalueMap(
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
            ));
        $this->connectionMock->expects($this->any())
            ->method('_quote')
            ->will($this->returnArgument(0));

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connectionMock));
        $this->resourceMock->expects($this->any())
            ->method('getMainTable')
            ->will($this->returnValue('some_main_table'));
        $this->resourceMock->expects($this->any())
            ->method('getTable')
            ->will(
                $this->returnValue('some_extra_table')
            );
    }

    /**
     * Test that Magento\Eav\Model\ResourceModel\Attribute\Collection::_initSelect sets expressions
     * that can be properly quoted by Zend_Db_Expr::quoteIdentifier
     *
     * @dataProvider initSelectDataProvider
     */
    public function testInitSelect($column, $value, $expected)
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject('Magento\Customer\Model\ResourceModel\Attribute\Collection',
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
        $this->assertEquals($expected, $this->model->getSelectCountSql()->assemble());
    }

    public function initSelectDataProvider()
    {
        return [
            'main_table_expression' => [
                'col2', '1',
                'SELECT COUNT(DISTINCT main_table.attribute_id) FROM `some_main_table` AS `main_table`' . "\n"
                . ' INNER JOIN `some_extra_table` AS `additional_table`'
                . ' ON additional_table.attribute_id = main_table.attribute_id' . "\n"
                . ' LEFT JOIN `some_extra_table` AS `scope_table`'
                . ' ON scope_table.attribute_id = main_table.attribute_id'
                . ' AND scope_table.website_id = :scope_website_id'
                . ' WHERE (main_table.entity_type_id = :mt_entity_type_id)'
                . ' AND (IF(main_table.col2 IS NULL, scope_table.col2, main_table.col2) = 1)',
            ],
            'additional_table_expression' => [
                'col3', '2',
                'SELECT COUNT(DISTINCT main_table.attribute_id) FROM `some_main_table` AS `main_table`' . "\n"
                . ' INNER JOIN `some_extra_table` AS `additional_table`'
                . ' ON additional_table.attribute_id = main_table.attribute_id' . "\n"
                . ' LEFT JOIN `some_extra_table` AS `scope_table`'
                . ' ON scope_table.attribute_id = main_table.attribute_id'
                . ' AND scope_table.website_id = :scope_website_id'
                . ' WHERE (main_table.entity_type_id = :mt_entity_type_id)'
                . ' AND (IF(additional_table.col3 IS NULL, scope_table.col3, additional_table.col3) = 2)',
            ]
        ];
    }
}
