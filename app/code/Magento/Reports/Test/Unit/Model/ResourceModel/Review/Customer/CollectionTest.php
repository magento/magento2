<?php
/**
 * CollectionTest
 *
 * @copyright Copyright (c) 2017 Interactiv4
 * @author    oscar.recio@interactiv4.com
 */

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Review\Customer;

use Magento\Framework\DB\Select;
use Magento\Reports\Model\ResourceModel\Review\Customer\Collection;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    protected function setUp()
    {
        $this->selectMock = $this->createMock(Select::class);
    }

    public function testGetSelectCountSql()
    {
        /** @var $collection \PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->getMockBuilder(Collection::class)
            ->setMethods(['getSelect'])
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->atLeastOnce())->method('getSelect')->willReturn($this->selectMock);

        $this->selectMock->expects($this->atLeastOnce())->method('reset')->willReturnSelf();
        $this->selectMock->expects($this->exactly(1))->method('columns')->willReturnSelf();

        $this->assertEquals($this->selectMock, $collection->getSelectCountSql());
    }
}
