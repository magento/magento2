<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as ModelScopeInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for \Magento\Framework\App\Config\ScopeConfigInterface
 */
class AbstractConfigTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var AbstractConfigTesting|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $config;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
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
        $methodInterfaceMock = $this->getMockBuilder(MethodInterface::class)
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
        $methodInterfaceMock = $this->getMockBuilder(MethodInterface::class)
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
     * @case #1 This conf parameters must return AbstractConfig::PAYMENT_ACTION_SALE (isWppApiAvailable == false)
     * @case #2 This conf parameters must return configValue (isWppApiAvailable == true)
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
    public function testIsWppApiAvailable($returnMap, $expectedValue)
    {
        $this->config->setMethod('paypal_express');
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap($returnMap);

        $this->assertEquals($expectedValue, $this->config->isWppApiAvailable());
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

    /**
     * @return array
     */
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

    /**
     * Check bill me later active setting uses disable funding options
     *
     * @param string|null $disableFundingOptions
     * @param int $expectedFlag
     * @param bool $expectedValue
     *
     * @dataProvider isMethodActiveBmlDataProvider
     */
    public function testIsMethodActiveBml($disableFundingOptions, $expectedFlag, $expectedValue)
    {
        $this->scopeConfigMock->method('getValue')
            ->with(
                self::equalTo('paypal/style/disable_funding_options'),
                self::equalTo('store')
            )
            ->willReturn($disableFundingOptions);

        $this->scopeConfigMock->method('isSetFlag')
            ->with('payment/paypal_express_bml/active')
            ->willReturn($expectedFlag);

        self::assertEquals($expectedValue, $this->config->isMethodActive('paypal_express_bml'));
    }

    /**
     * @return array
     */
    public function isMethodActiveBmlDataProvider()
    {
        return [
            ['CREDIT,CARD,ELV', 0, false],
            ['CREDIT,CARD,ELV', 1, true],
            ['CREDIT', 0, false],
            ['CREDIT', 1, true],
            ['CARD', 0, true],
            ['CARD', 1, true],
            [null, 0, true],
            [null, 1, true]
        ];
    }

    /**
     * Checks a case, when notation code based on Magento edition.
     */
    public function testGetBuildNotationCode()
    {
        $productMetadata = $this->getMockBuilder(ProductMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productMetadata->method('getEdition')
            ->willReturn('SomeEdition');

        $objectManagerHelper = new ObjectManagerHelper($this);
        $objectManagerHelper->setBackwardCompatibleProperty(
            $this->config,
            'productMetadata',
            $productMetadata
        );

        self::assertEquals('Magento_2_SomeEdition', $this->config->getBuildNotationCode());
    }

    /**
     * Checks a case, when notation code should be provided from configuration.
     */
    public function testBuildNotationCodeFromConfig()
    {
        $notationCode = 'Magento_Cart_EditionFromConfig';

        $this->scopeConfigMock->method('getValue')
            ->with(self::equalTo('paypal/notation_code'), self::equalTo('stores'))
            ->willReturn($notationCode);

        self::assertEquals($notationCode, $this->config->getBuildNotationCode());
    }
}
