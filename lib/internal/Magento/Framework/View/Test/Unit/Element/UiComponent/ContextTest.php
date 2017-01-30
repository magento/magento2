<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for view Messages model
 */
namespace Magento\Framework\View\Test\Unit\Element\UiComponent;

use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context
     */
    protected $context;

    protected function setUp()
    {
        $pageLayout = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')->getMock();
        $request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $buttonProviderFactory =
            $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Control\ButtonProviderFactory')
                ->disableOriginalConstructor()
                ->getMock();
        $actionPoolFactory =
            $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Control\ActionPoolFactory')
                ->disableOriginalConstructor()
                ->getMock();
        $contentTypeFactory =
            $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\ContentType\ContentTypeFactory')
                ->disableOriginalConstructor()
                ->getMock();
        $urlBuilder = $this->getMockBuilder('Magento\Framework\UrlInterface')->getMock();
        $processor = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Processor')->getMock();
        $uiComponentFactory =
            $this->getMockBuilder('Magento\Framework\View\Element\UiComponentFactory')
                ->disableOriginalConstructor()
                ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $objectManagerHelper->getObject(
            'Magento\Framework\View\Element\UiComponent\Context',
            [
                'pageLayout'            => $pageLayout,
                'request'               => $request,
                'buttonProviderFactory' => $buttonProviderFactory,
                'actionPoolFactory'     => $actionPoolFactory,
                'contentTypeFactory'    => $contentTypeFactory,
                'urlBuilder'            => $urlBuilder,
                'processor'             => $processor,
                'uiComponentFactory'    => $uiComponentFactory
            ]
        );
    }

    /**
     * @dataProvider addComponentDefinitionDataProvider
     * @param array $components
     * @param array $expected
     */
    public function testAddComponentDefinition($components, $expected)
    {
        foreach ($components as $component) {
            $this->context->addComponentDefinition($component['name'], $component['config']);
        }
        $this->assertEquals($expected, $this->context->getComponentsDefinitions());
    }

    /**
     * @return array
     */
    public function addComponentDefinitionDataProvider()
    {
        return [
            [
                [
                    [
                        'name' => 'component_1_Name',
                        'config' => [
                            'component_1_config_name_1' => 'component_1_config_value_1',
                            'component_1_config_name_2' => [
                                'component_1_config_value_1',
                                'component_1_config_value_2',
                                'component_1_config_value_3',
                            ],
                            'component_1_config_name_3' => 'component_1_config_value_1'
                        ]
                    ],
                    [
                        'name' => 'component_2_Name',
                        'config' => [
                            'component_2_config_name_1' => 'component_2_config_value_1',
                            'component_2_config_name_2' => [
                                'component_2_config_value_1',
                                'component_2_config_value_2',
                                'component_2_config_value_3',
                            ],
                            'component_2_config_name_3' => 'component_2_config_value_1'
                        ]
                    ],
                    [
                        'name' => 'component_1_Name',
                        'config' => [
                            'component_1_config_name_4' => 'component_1_config_value_1',
                            'component_1_config_name_5' => [
                                'component_1_config_value_1',
                                'component_1_config_value_2',
                                'component_1_config_value_3',
                            ],
                            'component_1_config_name_6' => 'component_1_config_value_1'
                        ]
                    ],
                ],
                [
                    'component_1_Name' => [
                        'component_1_config_name_1' => 'component_1_config_value_1',
                        'component_1_config_name_2' => [
                            'component_1_config_value_1',
                            'component_1_config_value_2',
                            'component_1_config_value_3',
                        ],
                        'component_1_config_name_3' => 'component_1_config_value_1',
                        'component_1_config_name_4' => 'component_1_config_value_1',
                        'component_1_config_name_5' => [
                            'component_1_config_value_1',
                            'component_1_config_value_2',
                            'component_1_config_value_3',
                        ],
                        'component_1_config_name_6' => 'component_1_config_value_1'
                    ],
                    'component_2_Name' => [
                        'component_2_config_name_1' => 'component_2_config_value_1',
                        'component_2_config_name_2' => [
                            'component_2_config_value_1',
                            'component_2_config_value_2',
                            'component_2_config_value_3',
                        ],
                        'component_2_config_name_3' => 'component_2_config_value_1'
                    ]
                ]
            ]
        ];
    }
}
