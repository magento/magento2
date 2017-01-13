<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Model\View\Layout;

use Magento\Backend\Model\View\Layout\FilterInterface;
use Magento\Backend\Model\View\Layout\Filter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout\Data\Structure;
use Magento\Framework\View\Layout\ScheduledStructure;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FilterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $someFilterMock;

    /**
     * @var Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureMock;

    /**
     * @var ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduledStructureMock;

    /**
     * @var Filter
     */
    private $filter;

    public function setUp()
    {
        $this->someFilterMock = $this->getMockBuilder(FilterInterface::class)
            ->getMock();
        $this->structureMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scheduledStructureMock = $this->getMockBuilder(ScheduledStructure::class)
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->filter = $objectManager->getObject(
            Filter::class,
            [
                'filters' =>
                    [
                        'filter1' => $this->someFilterMock
                    ]
            ]
        );
    }

    public function testFilterElement()
    {
        $this->someFilterMock->expects($this->once())
            ->method('filterElement')
            ->with($this->scheduledStructureMock, $this->structureMock);
        $this->assertTrue($this->filter->filterElement($this->scheduledStructureMock, $this->structureMock));
    }
}
