<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

    /**
     * @var \Magento\Framework\View\Layout\Element
     */
    protected $element;

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
     * @dataProvider processDataProvider
     */
    public function testProcess($xml)
    {
        $scope = $this->getMock('Magento\Framework\App\ScopeInterface', [], [], '', false);
        $this->scopeResolver->expects($this->any())->method('getScope')->will($this->returnValue($scope));
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with('test', null, $scope)
            ->will($this->returnValue(false));
        $this->element = new \Magento\Framework\View\Layout\Element($xml);
        $scheduleStructure = $this->getMock('\Magento\Framework\View\Layout\ScheduledStructure', [], [], '', false);
        $this->context->expects($this->any())->method('getScheduledStructure')->will(
            $this->returnValue($scheduleStructure)
        );
        $this->helper->expects($this->any())->method('scheduleStructure')->with(
            $scheduleStructure,
            $this->element,
            $this->element
        )->willReturn($this->element->getAttribute('name'));

        $this->helper->expects($this->any())->method('setStructureElementData')->with(
            $this->element->getAttribute('name'),
            ['attributes' => ['group' => '', 'component' => 'listing']]
        );

        $this->model->process($this->context, $this->element, $this->element);
    }

    public function processDataProvider()
    {
        return [
            [
                '<ui_component name="cms_block_listing" component="listing" ifconfig="test"/>'
            ]
        ];
    }
}
