<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Background image renderer test
 */
namespace Magento\DesignEditor\Model\Editor\QuickStyles\Renderer;

class BackgroundImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\BackgroundImage::toCss
     * @dataProvider backgroundImageData
     */
    public function testToCss($expectedResult, $data)
    {
        /** @var $rendererModel \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\BackgroundImage */
        $rendererModel = $this->getMock(
            'Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\BackgroundImage',
            null,
            [],
            '',
            false
        );

        $this->assertEquals($expectedResult, $rendererModel->toCss($data));
    }

    /**
     * @covers \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\BackgroundImage::toCss
     * @dataProvider backgroundImageDataClearDefault
     */
    public function testToCssClearDefault($expectedResult, $data)
    {
        /** @var $rendererModel \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\BackgroundImage */
        $rendererModel = $this->getMock(
            'Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\BackgroundImage',
            null,
            [],
            '',
            false
        );

        $this->assertEquals($expectedResult, $rendererModel->toCss($data));
    }

    /**
     * @return array
     */
    public function backgroundImageData()
    {
        return [
            [
                'expected_result' => ".header { background-image: url('path/image.gif'); }",
                'data' => [
                    'type' => 'image-uploader',
                    'default' => 'bg.gif',
                    'selector' => '.header',
                    'attribute' => 'background-image',
                    'value' => 'path/image.gif',
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    public function backgroundImageDataClearDefault()
    {
        return [
            [
                'expected_result' => ".header { background-image: none; }",
                'data' => [
                    'type' => 'image-uploader',
                    'default' => 'bg.gif',
                    'selector' => '.header',
                    'attribute' => 'background-image',
                    'value' => '',
                ],
            ]
        ];
    }
}
