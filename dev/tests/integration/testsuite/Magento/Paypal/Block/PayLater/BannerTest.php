<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Paypal\Block\PayLater;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\MutableScopeConfig;
use Magento\Framework\View\LayoutInterface;
use Magento\Paypal\Model\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class BannerTest extends TestCase
{
    /**
     * @magentoAppArea frontend
     * @dataProvider getJsLayoutDataProvider
     * @magentoAppIsolation enabled
     * @covers       \Magento\Paypal\Block\PayLater\Banner::getJsLayout()
     * @covers       \Magento\Paypal\Block\PayLater\Banner::getStyleAttributesConfig()
     */
    public function testGetJsLayout($systemConfig, $blockConfig, $expectedConfig)
    {
        $this->setConfig($systemConfig);

        /** @var LayoutInterface $layout */
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        $block = $layout->createBlock(Banner::class, '', ['data' => $blockConfig]);

        $jsConfig = json_decode($block->getJsLayout(), true);
        $this->assertArrayHasKey('config', $jsConfig['components']['payLater']);
        $this->assertArrayHasKey('component', $jsConfig['components']['payLater']);

        $optionsConfig = $jsConfig['components']['payLater']['config'];
        $this->assertEquals($expectedConfig, array_intersect_key($optionsConfig, $expectedConfig));
    }

    /**
     * @return array
     */
    public static function getJsLayoutDataProvider()
    {
        return [
            [
                'systemConfig' => [
                    'payment/paypal_paylater/test1page_stylelayout' => 'flex',
                    'payment/paypal_paylater/test1page_ratio' => '20x1',
                    'payment/paypal_paylater/test1page_color' => 'blue'
                ],
                'blockConfig' => [
                    'placement' => 'test1',
                    'jsLayout' => [
                        'components' => [
                            'payLater' => [
                                'config' => [
                                    'attributes' => [
                                        'data-pp-style-ratio' => '1x1'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                 'expectedConfig' => [
                    'attributes' => [
                        'data-pp-style-layout' => 'flex',
                        'data-pp-style-logo-type' => null,
                        'data-pp-style-logo-position' => null,
                        'data-pp-style-text-color' => null,
                        'data-pp-style-text-size' => null,
                        'data-pp-style-color' => 'blue',
                        'data-pp-style-ratio' => '1x1',
                        'data-pp-placement' => 'test1'
                    ]
                ]
            ],
            [
                'systemConfig' => [
                    'payment/paypal_paylater/test2page_stylelayout' => 'text',
                    'payment/paypal_paylater/test2page_logotype' => 'primary',
                    'payment/paypal_paylater/test2page_logoposition' => 'left',
                    'payment/paypal_paylater/test2page_textcolor' => 'white',
                    'payment/paypal_paylater/test2page_textsize' => '10'

                ],
                'blockConfig' => [
                    'placement' => 'test2',
                    'jsLayout' => [
                        'components' => [
                            'payLater' => [
                                'config' => [
                                    'attributes' => [
                                        'data-pp-style-text-color' => 'black'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'expectedConfig' => [
                    'attributes' => [
                        'data-pp-style-layout' => 'text',
                        'data-pp-style-logo-type' => 'primary',
                        'data-pp-style-logo-position' => 'left',
                        'data-pp-style-text-color' => 'black',
                        'data-pp-style-text-size' => '10',
                        'data-pp-style-color' => null,
                        'data-pp-style-ratio' => null,
                        'data-pp-placement' => 'test2'
                    ]
                ]
            ],
        ];
    }

    /**
     * @magentoAppArea frontend
     * @dataProvider sdkUrlDataProvider
     * @covers \Magento\Paypal\Block\PayLater\Banner::getJsLayout()
     * @covers \Magento\Paypal\Block\PayLater\Banner::getPayPalSdkUrl()
     */
    public function testSdkUrl($blockConfig, $expectedUrl)
    {
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        $block = $layout->createBlock(Banner::class, '', ['data' => $blockConfig]);

        $jsConfig = json_decode($block->getJsLayout(), true);
        $this->assertArrayHasKey('config', $jsConfig['components']['payLater']);
        $this->assertArrayHasKey('sdkUrl', $jsConfig['components']['payLater']['config']);
        $this->assertStringContainsString($expectedUrl, $jsConfig['components']['payLater']['config']['sdkUrl']);
    }

    public static function sdkUrlDataProvider()
    {
        return [
            [
                'blockConfig' => [
                    'jsLayout' => [
                        'components' => [
                            'payLater' => [
                                'config' => [
                                ]
                            ]
                        ]
                    ]
                ],
                'expectedUrl' => 'paypal.com/sdk'
            ],
            [
                'blockConfig' => [
                    'jsLayout' => [
                        'components' => [
                            'payLater' => [
                                'config' => [
                                    'attributes' => ['test1' => 'value1']
                                ]
                            ]
                        ]
                    ]
                ],
                'expectedUrl' => 'paypal.com/sdk'
            ],
            [
                'blockConfig' => [
                    'jsLayout' => [
                        'components' => [
                            'payLater' => [
                                'config' => [
                                    'sdkUrl' => 'http://mock.url'
                                ]
                            ]
                        ]
                    ]
                ],
                'expectedUrl' => 'mock.url'
            ]
        ];
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testToHtml()
    {
        $paymentMethod = sprintf('payment/%s/active', Config::METHOD_EXPRESS);
        $systemConfig = [
                $paymentMethod => 1,
                'payment/paypal_paylater/experience_active' => 1,
                'payment/paypal_paylater/enabled' => 1,
                'payment/paypal_paylater/test3page_display' => 1,
                'payment/paypal_paylater/test3page_position' => 'header'
        ];
        $blockConfig = [
            'placement' => 'test3',
            'position' => 'header'
        ];
        $this->setConfig($systemConfig);
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);

        /** @var Banner $block */
        $block = $layout->createBlock(Banner::class, '', ['data' => $blockConfig]);
        $block->setTemplate('Magento_Paypal::paylater/banner.phtml');

        $this->assertNotEmpty($block->toHtml());
    }

    /**
     * Check display configuration
     *
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @dataProvider toHtmlEmptyDataProvider
     * @param $systemConfig
     * @param $blockConfig
     */
    public function testToHtmlEmpty($systemConfig, $blockConfig)
    {
        //Enable all required options
        $paymentMethod = sprintf('payment/%s/active', Config::METHOD_EXPRESS);
        $enableSystemConfig = [
            $paymentMethod => 1,
            'payment/paypal_paylater/experience_active' => 1,
            'payment/paypal_paylater/enabled' => 1,
            'payment/paypal_paylater/test4page_display' => 1,
            'payment/paypal_paylater/test4page_position' => 'near_pp_button'
        ];
        $enableBlockConfig = [
            'placement' => 'test4',
            'position' => 'near_pp_button'
        ];
        //Disable specific system configuration option
        $systemConfig = array_replace($enableSystemConfig, $systemConfig);
        // Update block config
        $blockConfig = array_replace($enableBlockConfig, $blockConfig);
        $this->setConfig($systemConfig);
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        /** @var Banner $block */
        $block = $layout->createBlock(Banner::class, '', ['data' => $blockConfig]);
        $block->setTemplate('Magento_Paypal::paylater/banner.phtml');

        $this->assertEmpty($block->toHtml());
    }

    /**
     * @return array[]
     */
    public static function toHtmlEmptyDataProvider()
    {
        $paymentPath = 'payment/%s/active';
        return [
            [
                'systemConfig' => ['payment/paypal_paylater/experience_active' => 0],
                'blockConfig' => []
            ],
            [
                'systemConfig' => ['payment/paypal_paylater/enabled' => 0],
                'blockConfig' => []
            ],
            [
                'systemConfig' => ['payment/paypal_paylater/test4page_display' => 0],
                'blockConfig' => []
            ],
            [
                'systemConfig' => [],
                'blockConfig' => ['position' => 'header']
            ],
            [
                'systemConfig' => [sprintf($paymentPath, Config::METHOD_EXPRESS) => 0],
                'blockConfig' => []
            ],
            [
                'systemConfig' => ['paypal/style/disable_funding_options' => 'CREDIT'],
                'blockConfig' => []
            ],
        ];
    }

    /**
     * Set system configuration value for test
     *
     * @param $config
     */
    private function setConfig($config)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var $scopeConfig MutableScopeConfig */
        $scopeConfig = $objectManager->get(MutableScopeConfigInterface::class);
        foreach ($config as $path => $value) {
            $scopeConfig->setValue($path, $value, ScopeInterface::SCOPE_STORE);
        }
    }
}
