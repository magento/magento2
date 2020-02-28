<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Review\Customer;

use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Model\ResourceModel\Review\Customer\Collection;

/**
 *  Review product collection test
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->selectMock = $this->createMock(Select::class);
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->setMethods(['getSelect'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionMock->expects($this->atLeastOnce())->method('getSelect')->willReturn($this->selectMock);
    }

    public function testGetSelectCountSqlWithoutHavingClauses()
    {
        $havingClauses = [];
        $whereClauses = [];
        $this->selectMock->expects($this->atLeastOnce())->method('getPart')->willReturn($havingClauses);
        $this->selectMock->expects($this->atLeastOnce())->method('getPart')->willReturn($whereClauses);
        $this->selectMock->expects($this->at(2))->method('reset')->with(Select::ORDER);
        $this->selectMock->expects($this->at(3))->method('reset')->with(Select::LIMIT_COUNT);
        $this->selectMock->expects($this->at(4))->method('reset')->with(Select::LIMIT_OFFSET);
        $this->selectMock->expects($this->at(5))->method('reset')->with(Select::WHERE);
        $this->selectMock->expects($this->at(6))->method('reset')->with(Select::HAVING);
        $this->selectMock->expects($this->atLeastOnce())->method('columns')
            ->with(new \Zend_Db_Expr('COUNT(DISTINCT detail.customer_id)'))->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())->method('reset')->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())->method('from')->willReturnSelf();

        $this->assertEquals($this->selectMock, $this->collectionMock->getSelectCountSql());
    }

    public function testGetSelectCountSqlWithHavingClauses()
    {
        $havingClauses = [
            'clause-1' => '(review_cnt LIKE %4%)',
            'clause-2' => '(avg_rating LIKE %55.00%)'
        ];
        $whereClauses = [
            'customer name LIKE %test%'
        ];

        $this->selectMock->expects($this->atLeastOnce())->method('getPart')->willReturn($havingClauses);
        $this->selectMock->expects($this->atLeastOnce())->method('getPart')->willReturn($whereClauses);
        $this->selectMock->expects($this->at(2))->method('reset')->with(Select::ORDER);
        $this->selectMock->expects($this->at(3))->method('reset')->with(Select::LIMIT_COUNT);
        $this->selectMock->expects($this->at(4))->method('reset')->with(Select::LIMIT_OFFSET);
        $this->selectMock->expects($this->atLeastOnce())->method('reset')->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())->method('from')->willReturnSelf();

        $this->assertEquals($this->selectMock, $this->collectionMock->getSelectCountSql());
    }
}
