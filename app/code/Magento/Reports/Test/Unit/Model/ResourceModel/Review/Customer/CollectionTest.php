<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Review\Customer;

use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Model\ResourceModel\Review\Customer\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *  Review product collection test
 */
class CollectionTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MockObject
     */
    private $selectMock;

    /**
     * @var MockObject
     */
    private $collectionMock;

    protected function setUp(): void
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
