<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme css file model class
 */
namespace Magento\DesignEditor\Model\Editor\QuickStyles;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider sampleData
     */
    public function testRender($expectedResult, $data)
    {
        /** @var $rendererModel \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer */
        $rendererModel = $this->getMock(
            'Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer',
            null,
            [],
            '',
            false
        );

        $objectManager = $this->getMock('Magento\Framework\Object', ['get', 'toCss'], [], '', false);

        $objectManager->expects($this->exactly(4))->method('get')->will($this->returnValue($objectManager));

        $objectManager->expects($this->exactly(4))->method('toCss')->will($this->returnValue('css_string'));

        $property = new \ReflectionProperty($rendererModel, '_quickStyleFactory');
        $property->setAccessible(true);
        $property->setValue($rendererModel, $objectManager);

        $this->assertEquals($expectedResult, $rendererModel->render($data));
    }

    /**
     * @return array
     */
    public function sampleData()
    {
        return [
            [
                'expected_result' => "css_string\ncss_string\ncss_string\ncss_string\n",
                'data' => [
                    'header-background' => [
                        'type' => 'background',
                        'components' => [
                            'header-background:color-picker' => [
                                'type' => 'color-picker',
                                'default' => 'transparent',
                                'selector' => '.header',
                                'attribute' => 'background-color',
                                'value' => '#FFFFFF',
                            ],
                            'header-background:background-uploader' => [
                                'type' => 'background-uploader',
                                'components' => [
                                    'header-background:image-uploader' => [
                                        'type' => 'image-uploader',
                                        'default' => 'bg.gif',
                                        'selector' => '.header',
                                        'attribute' => 'background-image',
                                        'value' => '../image.jpg',
                                    ],
                                    'header-background:tile' => [
                                        'type' => 'checkbox',
                                        'default' => 'no-repeat',
                                        'options' => ['no-repeat', 'repeat', 'repeat-x', 'repeat-y', 'inherit'],
                                        'selector' => '.header',
                                        'attribute' => 'background-repeat',
                                        'value' => 'checked',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'menu-background' => [
                        'type' => 'color-picker',
                        'default' => '#f8f8f8',
                        'selector' => '.menu',
                        'attribute' => 'color',
                        'value' => '#000000',
                    ],
                ],
            ]
        ];
    }
}
