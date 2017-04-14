<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\View\Layout\Reader\UiComponent
 */
namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout\Reader\Context;
use Magento\Framework\View\Layout\Reader\UiComponent;
use Magento\Framework\View\Layout\Reader\Visibility\Condition;
use Magento\Framework\View\Layout\ScheduledStructure\Helper;

class UiComponentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\Reader\UiComponent
     */
    protected $model;

    /**
     * @var Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    protected function setUp()
    {
        $this->helper = $this->getMockBuilder(Helper::class)
            ->setMethods(['scheduleStructure'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->getMockBuilder(Context::class)
            ->setMethods(['getScheduledStructure', 'setElementToIfconfigList'])
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $condition = $objectManager->getObject(Condition::class);
        $this->model = new UiComponent($this->helper, $condition);
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
        $scheduleStructure = $this->getMock(
            \Magento\Framework\View\Layout\ScheduledStructure::class,
            [],
            [],
            '',
            false
        );
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
            [
                'attributes' => [
                    'group' => '',
                    'component' => 'listing',
                    'aclResource' => 'test_acl',
                    'visibilityConditions' => [
                        'ifconfig' => [
                            'name' => 'Magento\Framework\View\Layout\ConfigCondition',
                            'arguments' => [
                                'configPath' => 'config_path'
                            ],
                        ],
                        'acl' => [
                            'name' => 'Magento\Framework\View\Layout\AclCondition',
                            'arguments' => [
                                'acl' => 'test_acl'
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->model->interpret($this->context, $element);
    }

    public function interpretDataProvider()
    {
        return [
            [
                $this->getElement(
                    '<uiComponent
                        name="cms_block_listing"
                        aclResource="test_acl"
                        component="listing"
                        ifconfig="config_path"
                    ><visibilityCondition name="test_name" className="name"></visibilityCondition></uiComponent>',
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
            \Magento\Framework\View\Layout\Element::class
        );
        return $xml->{$elementType};
    }
}
