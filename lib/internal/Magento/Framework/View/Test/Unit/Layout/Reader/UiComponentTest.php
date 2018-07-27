<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\View\Layout\Reader\UiComponent
 */
namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use \Magento\Framework\View\Layout\Reader\UiComponent;

class UiComponentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\Reader\UiComponent
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    protected function setUp()
    {
        $this->helper = $this->getMockBuilder('Magento\Framework\View\Layout\ScheduledStructure\Helper')
            ->setMethods(['scheduleStructure'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Context')
            ->setMethods(['getScheduledStructure', 'setElementToIfconfigList'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new UiComponent($this->helper, 'scope');
    }

    public function testGetSupportedNodes()
    {
        $data[] = \Magento\Framework\View\Layout\Reader\UiComponent::TYPE_UI_COMPONENT;
        $this->assertEquals($data, $this->model->getSupportedNodes());
    }

    /**
     *
     * @param \Magento\Framework\View\Layout\Element $element
     *
     * @dataProvider interpretDataProvider
     */
    public function testInterpret($element)
    {
        $scheduleStructure = $this->getMock('\Magento\Framework\View\Layout\ScheduledStructure', [], [], '', false);
        $this->context->expects($this->any())->method('getScheduledStructure')->will(
            $this->returnValue($scheduleStructure)
        );
        $this->helper->expects($this->any())->method('scheduleStructure')->with(
            $scheduleStructure,
            $element,
            $element->getParent()
        )->willReturn($element->getAttribute('name'));

        $scheduleStructure->expects($this->once())->method('setStructureElementData')->with(
            $element->getAttribute('name'),
            ['attributes' => ['group' => '', 'component' => 'listing']]
        );
        $scheduleStructure->expects($this->once())->method('setElementToIfconfigList')->with(
            $element->getAttribute('name'),
            'config_path',
            'scope'
        );
        $this->model->interpret($this->context, $element);
    }

    /**
     * @return array
     */
    public function interpretDataProvider()
    {
        return [
            [
                $this->getElement(
                    '<uiComponent name="cms_block_listing" component="listing" ifconfig="config_path"/>',
                    'uiComponent'
                ),
            ]
        ];
    }

    /**
     * @param string $xml
     * @param string $elementType
     * @return \Magento\Framework\View\Layout\Element
     */
    protected function getElement($xml, $elementType)
    {
        $xml = simplexml_load_string(
            '<parent_element>' . $xml . '</parent_element>',
            'Magento\Framework\View\Layout\Element'
        );
        return $xml->{$elementType};
    }
}
