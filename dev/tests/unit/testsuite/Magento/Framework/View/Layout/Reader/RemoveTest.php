<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\View\Layout\Reader\Remove
 */
namespace Magento\Framework\View\Layout\Reader;

class RemoveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\Reader\Remove
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\View\Layout\Element
     */
    protected $element;

    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scheduledStructure;

    public function setUp()
    {
        $this->context = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scheduledStructure = $this->getMockBuilder('Magento\Framework\View\Layout\ScheduledStructure')
            ->disableOriginalConstructor()->setMethods(['setElementToRemoveList', '__wakeup'])
            ->getMock();
        $this->model = new Remove();
    }

    public function testGetSupportedNodes()
    {
        $data[] = \Magento\Framework\View\Layout\Reader\Remove::TYPE_REMOVE;
        $this->assertEquals($data, $this->model->getSupportedNodes());
    }

    /**
     * @dataProvider processDataProvider
     */
    public function testProcess($xml)
    {
        $this->element = new \Magento\Framework\View\Layout\Element($xml);
        $this->context->expects($this->any())
            ->method('getScheduledStructure')
            ->will($this->returnValue($this->scheduledStructure));
        $this->scheduledStructure->expects($this->once())->method('setElementToRemoveList')->with('header');
        $this->model->interpret($this->context, $this->element, $this->element);
    }

    public function processDataProvider()
    {
        return [
            [
                '<remove name="header"/>',
            ]
        ];
    }
}
