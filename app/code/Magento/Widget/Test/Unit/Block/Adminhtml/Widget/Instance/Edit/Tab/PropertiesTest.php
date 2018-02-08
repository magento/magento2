<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Test\Unit\Block\Adminhtml\Widget\Instance\Edit\Tab;

class PropertiesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $widget;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Properties
     */
    protected $propertiesBlock;

    protected function setUp()
    {
        $this->widget = $this->getMock('Magento\Widget\Model\Widget\Instance', [], [], '', false);
        $this->registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->propertiesBlock = $objectManager->getObject(
            'Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Properties',
            [
                'registry' => $this->registry
            ]
        );
    }

    /**
     * @param array $widgetConfig
     * @param boolean $isHidden
     *
     * @dataProvider isHiddenDataProvider
     */
    public function testIsHidden($widgetConfig, $isHidden)
    {
        $this->widget->expects($this->atLeastOnce())->method('getWidgetConfigAsArray')->willReturn($widgetConfig);

        $this->registry->expects($this->atLeastOnce())
            ->method('registry')
            ->with('current_widget_instance')
            ->willReturn($this->widget);

        $this->assertEquals($isHidden, $this->propertiesBlock->isHidden());
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
                'isHidden' => true
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
                'isHidden' => false
            ],
            [
                'widgetConfig' => [],
                'isHidden' => true
            ],
            [
                'widgetConfig' => [
                    'parameters' => [
                        'template' => [
                            'type' => 'select',
                            'visible' => '0',
                        ],
                    ]
                ],
                'isHidden' => true
            ]
        ];
    }
}
