<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Paypal\Block\Paylater;

use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;

class BannerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppArea frontend
     * @dataProvider getJsLayoutDataProvider
     * @magentoConfigFixture current_store payment/paypal_paylater/test1page_stylelayout flex
     * @magentoConfigFixture current_store payment/paypal_paylater/test1page_ratio 20x1
     * @magentoConfigFixture current_store payment/paypal_paylater/test1page_color blue
     * @magentoConfigFixture current_store payment/paypal_paylater/test2page_stylelayout text
     * @magentoConfigFixture current_store payment/paypal_paylater/test2page_logotype primary
     * @magentoConfigFixture current_store payment/paypal_paylater/test2page_logoposition left
     * @magentoConfigFixture current_store payment/paypal_paylater/test2page_textcolor white
     * @magentoConfigFixture current_store payment/paypal_paylater/test2page_textsize 10
     * @covers       \Magento\Paypal\Block\PayLater\Banner::getJsLayout()
     * @covers       \Magento\Paypal\Block\PayLater\Banner::getStyleAttributesConfig()
     */
    public function testGetJsLayout($blockConfig, $expectedConfig)
    {
        /** @var LayoutInterface $layout */
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        $block = $layout->createBlock(
            \Magento\Paypal\Block\PayLater\Banner::class,
            '',
            ['data' => $blockConfig]
        );

        $jsConfig = json_decode($block->getJsLayout(), true);
        $this->assertArrayHasKey('config', $jsConfig['components']['payLater']);
        $optionsConfig = $jsConfig['components']['payLater']['config'];
        $this->assertEquals($expectedConfig, array_intersect_key($optionsConfig, $expectedConfig));
    }

    /**
     * @return array
     */
    public function getJsLayoutDataProvider()
    {
        return [
            [
                [
                    'placement' => 'test1',
                    'position' => 'header',
                    'jslayout' => [
                        'components' => [
                            'payLater' => [
                                'config' => [
                                    'attributes' => [
                                        'data-pp-placement' => 'test1'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'attributes' => [
                        'data-pp-style-layout' => 'flex',
                        'data-pp-style-logo-type' => null,
                        'data-pp-style-logo-position' => null,
                        'data-pp-style-text-color' => null,
                        'data-pp-style-text-size' => null,
                        'data-pp-style-color' => 'blue',
                        'data-pp-style-ratio' => '20x1',
                    ]
                ]
            ],
            [
                [
                    'placement' => 'test2',
                    'position' => 'sidebar',
                    'jslayout' => [
                        'components' => [
                            'payLater' => [
                                'config' => [
                                    'attributes' => [
                                        'data-pp-placement' => 'test2'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'attributes' => [
                        'data-pp-style-layout' => 'text',
                        'data-pp-style-logo-type' => 'primary',
                        'data-pp-style-logo-position' => 'left',
                        'data-pp-style-text-color' => 'white',
                        'data-pp-style-text-size' => '10',
                        'data-pp-style-color' => null,
                        'data-pp-style-ratio' => null,
                    ]
                ]
            ],
        ];
    }

    /**
     * @magentoAppArea frontend
     * @covers \Magento\Paypal\Block\PayLater\Banner::getJsLayout()
     * @covers \Magento\Paypal\Block\PayLater\Banner::getPayPalSdkUrl()
     */
    public function testSdkUrl()
    {
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        $block = $layout->createBlock(
            \Magento\Paypal\Block\PayLater\Banner::class,
            '',
            []
        );

        $jsConfig = json_decode($block->getJsLayout(), true);
        $this->assertArrayHasKey('config', $jsConfig['components']['payLater']);
        $this->assertArrayHasKey('sdkUrl', $jsConfig['components']['payLater']['config']);
    }
}
