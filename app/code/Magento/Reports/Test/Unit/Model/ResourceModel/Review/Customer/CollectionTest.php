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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->selectMock = $this->createMock(Select::class);
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->onlyMethods(['getSelect'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionMock->expects($this->atLeastOnce())->method('getSelect')->willReturn($this->selectMock);
    }

    /**
     * @return void
     */
    public function testGetSelectCountSqlWithoutHavingClauses(): void
    {
        $havingClauses = [];
        $whereClauses = [];
        $this->selectMock->expects($this->atLeastOnce())->method('getPart')->willReturn($havingClauses);
        $this->selectMock->expects($this->atLeastOnce())->method('getPart')->willReturn($whereClauses);
        $this->selectMock
            ->method('reset')
            ->withConsecutive(
                [Select::ORDER],
                [Select::LIMIT_COUNT],
                [Select::LIMIT_OFFSET],
                [Select::WHERE],
                [Select::HAVING]
            );
        $this->selectMock->expects($this->atLeastOnce())->method('columns')
            ->with(new \Zend_Db_Expr('COUNT(DISTINCT detail.customer_id)'))->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())->method('reset')->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())->method('from')->willReturnSelf();

        $this->assertEquals($this->selectMock, $this->collectionMock->getSelectCountSql());
    }

    /**
     * @return void
     */
    public function testGetSelectCountSqlWithHavingClauses(): void
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
        $this->selectMock
            ->method('reset')
            ->withConsecutive([Select::ORDER], [Select::LIMIT_COUNT], [Select::LIMIT_OFFSET]);
        $this->selectMock->expects($this->atLeastOnce())->method('reset')->willReturnSelf();
        $this->selectMock->expects($this->atLeastOnce())->method('from')->willReturnSelf();

        $this->assertEquals($this->selectMock, $this->collectionMock->getSelectCountSql());
    }
}
