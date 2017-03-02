<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as ModelScopeInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AbstractConfigTest
 * @package Magento\Paypal\Test\Unit\Model
 */
class AbstractConfigTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var AbstractConfigTesting|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->setMethods(['getValue', 'isSetFlag'])
            ->getMockForAbstractClass();

        $this->config = new AbstractConfigTesting($this->scopeConfigMock);
    }

    /**
     * @param string|MethodInterface $method
     * @param $expected
     * @dataProvider setMethodDataProvider
     */
    public function testSetMethod($method, $expected)
    {
        $this->assertSame($this->config, $this->config->setMethod($method));
        $this->assertEquals($expected, $this->config->getMethodCode());
    }

    public function testSetMethodInstance()
    {
        /** @var $methodInterfaceMock MethodInterface */
        $methodInterfaceMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $this->assertSame($this->config, $this->config->setMethodInstance($methodInterfaceMock));
    }

    /**
     * @case #1 The method value is string - we expected same string value
     * @case #2 The method value is instance of MethodInterface - we expect result MethodInterface::getCode
     * @case #3 The method value is not string and not instance of MethodInterface - we expect null
     *
     * @return array
     */
    public function setMethodDataProvider()
    {
        /** @var $methodInterfaceMock MethodInterface */
        $methodInterfaceMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $methodInterfaceMock->expects($this->once())
            ->method('getCode')
            ->willReturn('payment_code');
        return [
            ['payment_code', 'payment_code'],
            [$methodInterfaceMock, 'payment_code'],
            [['array'], null]
        ];
    }

    public function testGetMethod()
    {
        $this->config->setMethod('method');
        $this->assertEquals('method', $this->config->getMethodCode());
    }

    public function testSetStoreId()
    {
        $this->assertSame($this->config, $this->config->setStoreId(1));
    }

    /**
     * @param string $key
     * @param string $method
     * @param array $returnMap
     * @param string $expectedValue
     *
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($key, $method, $returnMap, $expectedValue)
    {
        $this->config->setMethod($method);
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap($returnMap);

        $this->assertEquals($expectedValue, $this->config->getValue($key));
    }

    /**
     *
     * @case #1 This conf parameters must return AbstractConfig::PAYMENT_ACTION_SALE (isWppApiAvailabe == false)
     * @case #2 This conf parameters must return configValue (isWppApiAvailabe == true)
     * @case #3 This conf parameters must return configValue ($key != 'payment_action')
     * @case #4 This conf parameters must return configValue (configValue == 'Sale')
     * @case #5 This conf parameters must return configValue (shouldUseUnilateralPayments == false)
     * @case #6 This conf parameters must return configValue (method != METHOD_WPP_EXPRESS)
     *
     * @return array
     */
    public function getValueDataProvider()
    {
        return [
            [
                'payment_action',
                AbstractConfigTesting::METHOD_WPP_EXPRESS,
                [
                    ['payment/paypal_express/payment_action', ModelScopeInterface::SCOPE_STORE, null, 'notSaleValue'],
                    ['payment/paypal_express/business_account', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_username', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_password', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_signature', ModelScopeInterface::SCOPE_STORE, null, 0],
                    ['payment/paypal_express/api_cert', ModelScopeInterface::SCOPE_STORE, null, 0],
                ],
                AbstractConfigTesting::PAYMENT_ACTION_SALE
            ],
            [
                'payment_action',
                AbstractConfigTesting::METHOD_WPP_EXPRESS,
                [
                    ['payment/paypal_express/payment_action', ModelScopeInterface::SCOPE_STORE, null, 'configValue'],
                    ['payment/paypal_express/business_account', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_username', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_password', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_signature', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_cert', ModelScopeInterface::SCOPE_STORE, null, 0],
                ],
                'configValue'
            ],
            [
                'payment_other',
                AbstractConfigTesting::METHOD_WPP_EXPRESS,
                [
                    ['payment/paypal_express/payment_other', ModelScopeInterface::SCOPE_STORE, null, 'configValue'],
                ],
                'configValue'
            ],
            [
                'payment_action',
                AbstractConfigTesting::METHOD_WPP_EXPRESS,
                [
                    ['payment/paypal_express/payment_action', ModelScopeInterface::SCOPE_STORE, null, 'Sale'],
                ],
                'Sale'
            ],
            [
                'payment_action',
                AbstractConfigTesting::METHOD_WPP_EXPRESS,
                [
                    ['payment/paypal_express/payment_action', ModelScopeInterface::SCOPE_STORE, null, 'configValue'],
                    ['payment/paypal_express/business_account', ModelScopeInterface::SCOPE_STORE, null, 0],
                ],
                'configValue'
            ],
            [
                'payment_action',
                'method_other',
                [
                    ['payment/method_other/payment_action', ModelScopeInterface::SCOPE_STORE, null, 'configValue'],
                ],
                'configValue'
            ],
        ];
    }

    /**
     * @param array $returnMap
     * @param bool $expectedValue
     *
     * @dataProvider isWppApiAvailabeDataProvider
     */
    public function testIsWppApiAvailabe($returnMap, $expectedValue)
    {
        $this->config->setMethod('paypal_express');
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap($returnMap);

        $this->assertEquals($expectedValue, $this->config->isWppApiAvailabe());
    }

    /**
     * @return array
     */
    public function isWppApiAvailabeDataProvider()
    {
        return [
            [
                [
                    ['payment/paypal_express/api_username', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_password', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_signature', ModelScopeInterface::SCOPE_STORE, null, 0],
                    ['payment/paypal_express/api_cert', ModelScopeInterface::SCOPE_STORE, null, 0],
                ],
                false
            ],
            [
                [
                    ['payment/paypal_express/api_username', ModelScopeInterface::SCOPE_STORE, null, 0],
                ],
                false
            ],
            [
                [
                    ['payment/paypal_express/api_username', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_password', ModelScopeInterface::SCOPE_STORE, null, 0],
                ],
                false
            ],
            [
                [
                    ['payment/paypal_express/api_username', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_password', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_signature', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_cert', ModelScopeInterface::SCOPE_STORE, null, 0],
                ],
                true
            ],
            [
                [
                    ['payment/paypal_express/api_username', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_password', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_signature', ModelScopeInterface::SCOPE_STORE, null, 0],
                    ['payment/paypal_express/api_cert', ModelScopeInterface::SCOPE_STORE, null, 1],
                ],
                true
            ],
            [
                [
                    ['payment/paypal_express/api_username', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_password', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_signature', ModelScopeInterface::SCOPE_STORE, null, 1],
                    ['payment/paypal_express/api_cert', ModelScopeInterface::SCOPE_STORE, null, 1],
                ],
                true
            ],
        ];
    }

    /**
     * @param string|null $methodCode
     * @param bool $expectedFlag
     *
     * @dataProvider isMethodAvailableDataProvider
     */
    public function testIsMethodAvailable($methodCode, $expectedFlag)
    {
        $this->config->setMethod('settedMethod');
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with($expectedFlag);

        $this->config->isMethodAvailable($methodCode);
    }

    public function isMethodAvailableDataProvider()
    {
        return [
            [null, 'payment/settedMethod/active'],
            ['newMethod', 'payment/newMethod/active'],
        ];
    }

    public function testIsMethodActive()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with('payment/method/active');

        $this->config->isMethodActive('method');
    }

    public function testGetBuildNotationCode()
    {
        $productMetadata = $this->getMock(ProductMetadataInterface::class, [], [], '', false);
        $productMetadata->expects($this->once())
            ->method('getEdition')
            ->will($this->returnValue('SomeEdition'));

        $objectManagerHelper = new ObjectManagerHelper($this);
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->config,
            'productMetadata',
            $productMetadata
        );

        $this->assertEquals('Magento_Cart_SomeEdition', $this->config->getBuildNotationCode());
    }
}
