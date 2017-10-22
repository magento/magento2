<?php
/**
 * CollectionTest
 *
 * @copyright Copyright (c) 2017 Interactiv4
 * @author    oscar.recio@interactiv4.com
 */

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Report\Review\Customer;

use Magento\Reports\Model\ResourceModel\Review\Customer\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    protected function setUp()
    {
        $this->selectMock = $this->getMock('\Magento\Framework\DB\Select',
            [], [], '', false);
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
