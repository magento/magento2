<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Paypal\Model;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\MutableScopeConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class PayLaterConfigTest extends TestCase
{
    /**
     * @dataProvider getStyleDataProvider
     * @magentoAppIsolation enabled
     * @covers \Magento\Paypal\Model\PayLaterConfig::getSectionConfig()
     */
    public function testGetStyleConfig($systemConfig, $expectedConfig)
    {
        $this->setConfig($systemConfig);

        /** @var PayLaterConfig $config */
        $config = Bootstrap::getObjectManager()->get(PayLaterConfig::class);
        $this->assertEquals($expectedConfig, $config->getSectionConfig('test1', PayLaterConfig::CONFIG_KEY_STYLE));
    }

    /**
     * @return array
     */
    public function getStyleDataProvider()
    {
        return [
            [
                'systemConfig' => [
                    'payment/paypal_paylater/test1page_stylelayout' => 'flex',
                    'payment/paypal_paylater/test1page_ratio' => '20x1',
                    'payment/paypal_paylater/test1page_color' => 'blue'
                ],
                 'expectedConfig' => [
                    'data-pp-style-layout' => 'flex',
                    'data-pp-style-logo-type' => null,
                    'data-pp-style-logo-position' => null,
                    'data-pp-style-text-color' => null,
                    'data-pp-style-text-size' => null,
                    'data-pp-style-color' => 'blue',
                    'data-pp-style-ratio' => '20x1',
                ]
            ],
            [
                'systemConfig' => [
                    'payment/paypal_paylater/test1page_stylelayout' => 'text',
                    'payment/paypal_paylater/test1page_logotype' => 'primary',
                    'payment/paypal_paylater/test1page_logoposition' => 'left',
                    'payment/paypal_paylater/test1page_textcolor' => 'white',
                    'payment/paypal_paylater/test1page_textsize' => '10'

                ],
                'expectedConfig' => [
                    'data-pp-style-layout' => 'text',
                    'data-pp-style-logo-type' => 'primary',
                    'data-pp-style-logo-position' => 'left',
                    'data-pp-style-text-color' => 'white',
                    'data-pp-style-text-size' => '10',
                    'data-pp-style-color' => null,
                    'data-pp-style-ratio' => null,
                ]
            ],
        ];
    }

    /**
     * @dataProvider getPositionDataProvider
     * @magentoAppIsolation enabled
     * @covers \Magento\Paypal\Model\PayLaterConfig::getSectionConfig()
     */
    public function testGetPositionConfig($systemConfig, $expectedConfig)
    {
        $this->setConfig($systemConfig);

        /** @var PayLaterConfig $config */
        $config = Bootstrap::getObjectManager()->get(PayLaterConfig::class);
        $this->assertEquals($expectedConfig, $config->getSectionConfig('test1', PayLaterConfig::CONFIG_KEY_POSITION));
    }

    /**
     * @return array[]
     */
    public function getPositionDataProvider()
    {
        return [
            [
                'systemConfig' => [
                    'payment/paypal_paylater/test1page_position' => 'header',
                ],
                'expectedConfig' => 'header'
            ],
            [
                'systemConfig' => [
                    'payment/paypal_paylater/test1page_position' => 'sidebar',
                ],
                'expectedConfig' => 'sidebar'
            ],
            [
                'systemConfig' => [
                    'payment/paypal_paylater/test2page_position' => 'sidebar',
                ],
                'expectedConfig' => ''
            ],
        ];
    }

    /**
     * @magentoAppIsolation enabled
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled($systemConfig, $expected)
    {
        $systemConfig = array_replace([
                'payment/paypal_paylater/experience_active' => 1,
                'payment/paypal_paylater/enabled' => 1,
                'payment/paypal_paylater/test1page_display' => 1
        ], $systemConfig);

        $this->setConfig($systemConfig);
        /** @var PayLaterConfig $config */
        $config = Bootstrap::getObjectManager()->get(PayLaterConfig::class);
        $this->assertEquals($expected, $config->isEnabled('test1'));
    }

    /**
     * @return array[]
     */
    public function isEnabledDataProvider()
    {
        $paymentPath = 'payment/%s/active';
        return [
            'PayPal Express' => [
                [sprintf($paymentPath, Config::METHOD_EXPRESS) => 1],
                true
            ],
            'PayPal Express - Disabled' => [
                [sprintf($paymentPath, Config::METHOD_EXPRESS) => 0],
                false
            ],
            'PayPal Express - Disabled funding' => [
                [
                    sprintf($paymentPath, Config::METHOD_EXPRESS) => 1,
                    'paypal/style/disable_funding_options' => 'CARD,ELV'
                ],
                true
            ],
            'PayPal Express - Disabled funding CREDIT' => [
                [
                    sprintf($paymentPath, Config::METHOD_EXPRESS) => 1,
                    'paypal/style/disable_funding_options' => 'CREDIT,CARD,ELV'
                ],
                false
            ],
            'PayPal Standard Bml' => [
                [
                    sprintf($paymentPath, Config::METHOD_WPS_EXPRESS) => 1,
                    sprintf($paymentPath, Config::METHOD_WPS_BML) => 1,
                ],
                true
            ],
            'PayPal Standard Bml - Disabled' => [
                [
                    sprintf($paymentPath, Config::METHOD_WPS_EXPRESS) => 0,
                    sprintf($paymentPath, Config::METHOD_WPS_BML) => 1,
                ],
                false
            ],
            'PayPal Standard Bml - Disabled PP Credit' => [
                [
                    sprintf($paymentPath, Config::METHOD_WPS_EXPRESS) => 1,
                    sprintf($paymentPath, Config::METHOD_WPS_BML) => 0,
                ],
                false
            ],
            'PayPal Bill Me Later - Express Checkout (Payflow Edition)' => [
                [
                    sprintf($paymentPath, Config::METHOD_WPP_PE_EXPRESS) => 1,
                    sprintf($paymentPath, Config::METHOD_WPP_PE_BML) => 1,
                ],
                true
            ],
            'PayPal Bill Me Later - Express Checkout (Payflow Edition) - Disabled' => [
                [
                    sprintf($paymentPath, Config::METHOD_WPP_PE_EXPRESS) => 0,
                    sprintf($paymentPath, Config::METHOD_WPP_PE_BML) => 1,
                ],
                false
            ],
            'PayPal Bill Me Later - Express Checkout (Payflow Edition) - Disabled PP Credit' => [
                [
                    sprintf($paymentPath, Config::METHOD_WPP_PE_EXPRESS) => 1,
                    sprintf($paymentPath, Config::METHOD_WPP_PE_BML) => 0,
                ],
                false
            ],
            'PayLater disabled' => [
                [
                    sprintf($paymentPath, Config::METHOD_EXPRESS) => 1,
                    'payment/paypal_paylater/enabled' => 0
                ],
                false
            ],
            '"Display" for page disabled' => [
                [
                    sprintf($paymentPath, Config::METHOD_EXPRESS) => 1,
                    'payment/paypal_paylater/test1page_display' => 0
                ],
                false
            ],
            'PayLater experience not active' => [
                [
                    sprintf($paymentPath, Config::METHOD_EXPRESS) => 1,
                    'payment/paypal_paylater/experience_active' => 0
                ],
                false
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
