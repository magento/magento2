<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\Resource\Quote\Item;

use Magento\Framework\DB\Select;
use Magento\Framework\Object;
use Magento\Reports\Model\Resource\Quote\Item\Collection;

/**
 * Test class for \Magento\Reports\Model\Resource\Quote\Item\Collection
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\Resource\Quote\Item\Collection
     */
    protected $collection;

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
    protected $managerMock;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productResourceMock;

    /**
     * @var \Magento\Customer\Model\Resource\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerResourceMock;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderResourceMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMockBuilder('Magento\Framework\Data\Collection\EntityFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->getMock();
        $this->fetchStrategyMock = $this->getMockBuilder('Magento\Framework\Data\Collection\Db\FetchStrategyInterface')
            ->getMock();
        $this->managerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->getMock();
        $this->productResourceMock = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerResourceMock = $this->getMockBuilder('Magento\Customer\Model\Resource\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderResourceMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->collection = new Collection(
            $this->entityFactoryMock,
            $this->loggerMock,
            $this->fetchStrategyMock,
            $this->managerMock,
            $this->productResourceMock,
            $this->customerResourceMock,
            $this->orderResourceMock
        );
    }

    /**
     * @return void
     */
    public function testPrepareActiveCartItems()
    {
        $select = $this->collection->prepareActiveCartItems();
        $this->assertEquals(
            "SELECT `main_table`.`product_id`, COUNT(main_table.item_id) AS `carts`, `quote`.`base_to_global_rate` " .
            "FROM `quote_item` AS `main_table`\n " .
            "INNER JOIN `quote` ON main_table.quote_id = quote.entity_id WHERE (quote.is_active = 1) " .
            "GROUP BY `main_table`.`product_id`",
            $select->__toString()
        );
    }

    /**
     * @return void
     */
    public function testAddStoreFilter()
    {
        $this->collection->addStoreFilter([1, 2, 3]);
        $this->assertEquals(
            '(`main_table`.`store_id` IN(1, 2, 3))',
            $this->collection->getSelect()->getPart('where')[0]
        );
    }

    /**
     * @return void
     */
    public function testAddCustomerData()
    {
        $clonedSelect = clone $this->collection->getSelect();
        $connectionMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->setMethods(['select', 'fetchCol'])
            ->getMock();
        $connectionMock
            ->expects($this->any())
            ->method('select')
            ->willReturn($clonedSelect);
        $connectionMock
            ->expects($this->any())
            ->method('fetchCol')
            ->willReturn([100, 200]);

        $this->customerResourceMock
            ->expects($this->any())
            ->method('getReadConnection')
            ->willReturn($connectionMock);

        $this->customerResourceMock
            ->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValueMap(
                [
                    [
                        'firstname',
                        new Object(['attribute_id' => 111, 'backend' => new Object(['table' => 'firstname_table'])])
                    ],
                    [
                        'lastname',
                        new Object(['attribute_id' => 222, 'backend' => new Object(['table' => 'lastname_table'])])
                    ]
                ]
            ));

        $this->collection->addCustomerData(
            [
                'customer_name' => 'test name',
                'email' => 'test@email.com'
            ]
        );

        $this->assertEquals(
            'SELECT `main_table`.* FROM `quote_item` AS `main_table` WHERE (main_table.customer_id IN (100, 200))',
            $this->collection->getSelect()->__toString()
        );
    }

    /**
     * @return void
     */
    public function testGetSelectCountSql()
    {
        $this->assertEquals(
            "SELECT COUNT(DISTINCT main_table.product_id) FROM `quote_item` AS `main_table`\n " .
            "INNER JOIN `quote` ON main_table.quote_id = quote.entity_id WHERE (quote.is_active = 1)",
            $this->collection->getSelectCountSql()->__toString()
        );
    }

    /**
     * @return void
     */
    public function testAfterLoad()
    {
        $connectionMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->getMock();
        $select = new Select($connectionMock);

        $this->productResourceMock
            ->expects($this->any())
            ->method('getSelect')
            ->willReturn($select);
        $this->productResourceMock
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $this->productResourceMock
            ->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValueMap(
                [
                    [
                        'name',
                        new Object(['attribute_id' => 33, 'backend' => new Object(['table' => 'name_table'])])
                    ],
                    [
                        'price',
                        new Object(['attribute_id' => 44, 'backend' => new Object(['table' => 'price_table'])])
                    ]
                ]
            ));

        $orderConnectionMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->getMock();
        $orderConnectionMock
            ->expects($this->any())
            ->method('fetchAssoc')
            ->willReturn(
                [
                    100500 => ['io' => 999, 'product_id' => 100500, 'orders' => 789]
                ]
            );

        $this->orderResourceMock
            ->expects($this->any())
            ->method('getSelect')
            ->willReturn(new Select($orderConnectionMock));
        $this->orderResourceMock
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($orderConnectionMock);

        $this->entityFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will(
                $this->returnCallback(
                    function () {
                        return new Object();
                    }
                )
            );

        $this->fetchStrategyMock
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn(
                [
                    ['product_id' => 100500]
                ]
            );

        $this->collection->loadWithFilter();

        $this->assertCount(1, $this->collection->getItems());
        $this->assertEquals(
            [
                'product_id' => 100500,
                'id' => 100500,
                'price' => 0,
                'name' => null,
                'orders' => 789,
            ],
            $this->collection->getItems()[0]->getData()
        );
    }

}

