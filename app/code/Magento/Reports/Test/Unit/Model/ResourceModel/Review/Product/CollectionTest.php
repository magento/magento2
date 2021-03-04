<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Review\Product;

use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Model\ResourceModel\Review\Product\Collection;

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
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $selectMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
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
        $this->selectMock->expects($this->atLeastOnce())->method('getPart')->willReturn($havingClauses);
        $this->selectMock->expects($this->at(1))->method('reset')->with(Select::ORDER);
        $this->selectMock->expects($this->at(2))->method('reset')->with(Select::LIMIT_COUNT);
        $this->selectMock->expects($this->at(3))->method('reset')->with(Select::LIMIT_OFFSET);
        $this->selectMock->expects($this->atLeastOnce())->method('columns')
            ->with(new \Zend_Db_Expr('1'))->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())->method('resetJoinLeft')->willReturnSelf();

        $this->selectMock->expects($this->at(4))->method('reset')->with(Select::COLUMNS);
        $this->selectMock->expects($this->at(5))->method('reset')->with(Select::HAVING);
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

        $this->selectMock->expects($this->atLeastOnce())->method('getPart')->willReturn($havingClauses);
        $this->selectMock->expects($this->at(1))->method('reset')->with(Select::ORDER);
        $this->selectMock->expects($this->at(2))->method('reset')->with(Select::LIMIT_COUNT);
        $this->selectMock->expects($this->at(3))->method('reset')->with(Select::LIMIT_OFFSET);
        $this->selectMock->expects($this->atLeastOnce())->method('reset')->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())->method('from')->willReturnSelf();

        $this->assertEquals($this->selectMock, $this->collectionMock->getSelectCountSql());
    }
}
