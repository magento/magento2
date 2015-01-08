<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab;

class PropertiesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $widgetConfig
     * @param boolean $expected
     *
     * @dataProvider isHiddenDataProvider
     */
    public function testIsHidden($widgetConfig, $expected)
    {
        /** @var \Magento\Widget\Model\Widget\Instance|\PHPUnit_Framework_MockObject_MockObject $widget */
        $widget = $this->getMock('Magento\Widget\Model\Widget\Instance', [], [], '', false);
        $widget->expects($this->atLeastOnce())->method('getWidgetConfigAsArray')->willReturn($widgetConfig);

        /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject $registry */
        $registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $registry->expects($this->atLeastOnce())
            ->method('registry')
            ->with('current_widget_instance')
            ->willReturn($widget);

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Properties $propertiesBlock */
        $propertiesBlock = $objectManager->getObject(
            'Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Properties',
            [
                'registry' => $registry
            ]
        );

        $this->assertEquals($expected, $propertiesBlock->isHidden());
    }

    /**
     * @return array
     */
    public function isHiddenDataProvider()
    {
        return [
            [
                'widgetConfig' => [
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
                ],
                'expected' => true
            ],
            [
                'widgetConfig' => [
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
                ],
                'expected' => false
            ]
        ];
    }
}
