<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab;

class PropertiesTest extends \PHPUnit_Framework_TestCase
{
    public function testIsHiddenTrue()
    {
        $widgetConfig = [
            'parameters' => [
                'title' => [
                    'type' => 'text',
                    'visible' => '0',
                ],
                'template' => [
                    'type' => 'select',
                    'visible' => '1',
                ],
            ]
        ];

        /** @var \Magento\Widget\Model\Widget\Instance|\PHPUnit_Framework_MockObject_MockObject $widget */
        $widget = $this->getMock('Magento\Widget\Model\Widget\Instance', [], [], '', false);
        $widget->expects($this->atLeastOnce())
            ->method('getWidgetConfigAsArray')
            ->will($this->returnValue($widgetConfig));

        /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject $registry */
        $registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $registry->expects($this->atLeastOnce())
            ->method('registry')
            ->with('current_widget_instance')
            ->will($this->returnValue($widget));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Properties $propertiesBlock */
        $propertiesBlock = $objectManager->getObject(
            'Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Properties',
            [
                'registry' => $registry
            ]
        );

        $this->assertTrue($propertiesBlock->isHidden());
    }

    public function testIsHiddenFalse()
    {
        $widgetConfig = [
            'parameters' => [
                'types' => [
                    'type' => 'multiselect',
                    'visible' => '1',
                ],
                'template' => [
                    'type' => 'select',
                    'visible' => '1',
                ],
            ]
        ];

        /** @var \Magento\Widget\Model\Widget\Instance|\PHPUnit_Framework_MockObject_MockObject $widget */
        $widget = $this->getMock('Magento\Widget\Model\Widget\Instance', [], [], '', false);
        $widget->expects($this->atLeastOnce())
            ->method('getWidgetConfigAsArray')
            ->will($this->returnValue($widgetConfig));

        /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject $registry */
        $registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $registry->expects($this->atLeastOnce())
            ->method('registry')
            ->with('current_widget_instance')
            ->will($this->returnValue($widget));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Properties $propertiesBlock */
        $propertiesBlock = $objectManager->getObject(
            'Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Properties',
            [
                'registry' => $registry
            ]
        );

        $this->assertFalse($propertiesBlock->isHidden());
    }
}
