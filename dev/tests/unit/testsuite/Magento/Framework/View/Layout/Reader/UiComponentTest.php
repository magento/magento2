<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Test class for \Magento\Framework\View\Layout\Reader\UiComponent
 */
namespace Magento\Framework\View\Layout\Reader;

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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeResolver;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    public function setUp()
    {
        $this->helper = $this->getMockBuilder('Magento\Framework\View\Layout\ScheduledStructure\Helper')
            ->setMethods(['scheduleStructure'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')->getMock();
        $this->scopeResolver = $this->getMockForAbstractClass(
            'Magento\Framework\App\ScopeResolverInterface',
            [],
            '',
            false
        );
        $this->context = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Context')
            ->setMethods(['getScheduledStructure'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new UiComponent($this->helper, $this->scopeConfig, $this->scopeResolver, null);
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
        $scope = $this->getMock('Magento\Framework\App\ScopeInterface', [], [], '', false);
        $this->scopeResolver->expects($this->any())->method('getScope')->will($this->returnValue($scope));
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with('test', null, $scope)
            ->will($this->returnValue(false));
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
        $this->model->interpret($this->context, $element);
    }

    public function interpretDataProvider()
    {
        return [
            [
                $this->getElement('<ui_component name="cms_block_listing" component="listing" ifconfig="test"/>'),
            ]
        ];
    }

    /**
     * @param string $xml
     * @return \Magento\Framework\View\Layout\Element
     */
    protected function getElement($xml)
    {
        $xml = simplexml_load_string(
            '<parent_element>' . $xml . '</parent_element>',
            'Magento\Framework\View\Layout\Element'
        );
        return current($xml->children());
    }
}
