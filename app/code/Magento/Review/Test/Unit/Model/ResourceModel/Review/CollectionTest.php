<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Model\ResourceModel\Review;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Review\Model\ResourceModel\Review\Collection
     */
    protected $model;

    /**
     * @var \Magento\Framework\DB\Select | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerAdapterMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $store = $this->getMock('\Magento\Store\Model\Store', ['getId'], [], '', false);
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->objectManager = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this));
        $this->resourceMock = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\AbstractDb')
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getMainTable', 'getTable'])
            ->getMockForAbstractClass();
        $this->readerAdapterMock = $this->getMockBuilder('\Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->setMethods(['select', 'prepareSqlCondition', 'quoteInto'])
            ->getMockForAbstractClass();
        $this->selectMock = $this->getMockBuilder('\Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();
        $this->readerAdapterMock->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->readerAdapterMock);
        $this->resourceMock->expects($this->any())
            ->method('getMainTable')
            ->willReturn('maintable');
        $this->resourceMock->expects($this->any())
            ->method('getTable')
            ->willReturnCallback(function ($table) {
                return $table;
            });
        $this->model = $this->objectManager->getObject(
            '\Magento\Review\Model\ResourceModel\Review\Collection',
            [
                'storeManager' => $this->storeManagerMock,
                'resource' => $this->resourceMock,
            ]
        );

    }

    public function testInitSelect()
    {
        $this->selectMock->expects($this->once())
            ->method('join')
            ->with(
                ['detail' => 'review_detail'],
                'main_table.review_id = detail.review_id',
                ['detail_id', 'title', 'detail', 'nickname', 'customer_id']
            );
        $this->objectManager->getObject(
            '\Magento\Review\Model\ResourceModel\Review\Collection',
            [
                'storeManager' => $this->storeManagerMock,
                'resource' => $this->resourceMock,
            ]
        );
    }

    public function testAddStoreFilter()
    {
        $this->readerAdapterMock->expects($this->once())
            ->method('prepareSqlCondition');
        $this->selectMock->expects($this->once())
            ->method('join')
            ->with(
                ['store' => 'review_store'],
                'main_table.review_id=store.review_id',
                []
            );
        $this->model->addStoreFilter(1);
    }

    /**
     * @param int|string $entity
     * @param int $pkValue
     * @param string $quoteIntoArguments1
     * @param string $quoteIntoArguments2
     * @param string $quoteIntoReturn1
     * @param string $quoteIntoReturn2
     * @param int $callNum
     * @dataProvider addEntityFilterDataProvider
     */
    public function testAddEntityFilter(
        $entity,
        $pkValue,
        $quoteIntoArguments1,
        $quoteIntoArguments2,
        $quoteIntoReturn1,
        $quoteIntoReturn2,
        $callNum
    ) {
        $this->readerAdapterMock->expects($this->at(0))
            ->method('quoteInto')
            ->with($quoteIntoArguments1[0], $quoteIntoArguments1[1])
            ->willReturn($quoteIntoReturn1);
        $this->readerAdapterMock->expects($this->at(1))
            ->method('quoteInto')
            ->with($quoteIntoArguments2[0], $quoteIntoArguments2[1])
            ->willReturn($quoteIntoReturn2);
        $this->selectMock->expects($this->exactly($callNum))
            ->method('join')
            ->with(
                'review_entity',
                'main_table.entity_id=' . 'review_entity' . '.entity_id',
                ['entity_code']
            );
        $this->model->addEntityFilter($entity, $pkValue);
    }

    public function addEntityFilterDataProvider()
    {
        return [
            [
                1,
                2,
                ['main_table.entity_id=?', 1],
                ['main_table.entity_pk_value=?', 2],
                'quoteIntoReturn1',
                'quoteIntoReturn2',
                0
            ],
            [
                'entity',
                2,
                ['review_entity.entity_code=?', 'entity'],
                ['main_table.entity_pk_value=?', 2],
                'quoteIntoReturn1',
                'quoteIntoReturn2',
                1
            ]
        ];
    }

    public function testAddReviewsTotalCount()
    {
        $this->selectMock->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['r' => 'review'],
                'main_table.entity_pk_value = r.entity_pk_value',
                ['total_reviews' => new \Zend_Db_Expr('COUNT(r.review_id)')]
            )->willReturnSelf();
        $this->selectMock->expects($this->once())
            ->method('group');
        $this->model->addReviewsTotalCount();
    }
}
