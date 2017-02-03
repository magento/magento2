<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Model;

use Magento\Braintree\Model\Config;
use Magento\Braintree\Model\PaymentMethod;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ConfigTest
 *
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Model\Config
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Braintree\Model\Adapter\BraintreeConfiguration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $braintreeConfigurationMock;

    /**
     * @var \Magento\Framework\DB\TransactionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $braintreeClientTokenMock;

    /**
     * @var \Magento\Braintree\Model\System\Config\Source\Country|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sourceCountryMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->braintreeConfigurationMock = $this->getMockBuilder(
            '\Magento\Braintree\Model\Adapter\BraintreeConfiguration'
        )->disableOriginalConstructor()
            ->getMock();
        $this->sourceCountryMock = $this->getMockBuilder('\Magento\Braintree\Model\System\Config\Source\Country')
            ->disableOriginalConstructor()
            ->getMock();
        $this->braintreeClientTokenMock = $this->getMockBuilder('\Magento\Braintree\Model\Adapter\BraintreeClientToken')
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            '\Magento\Braintree\Model\Config',
            [
                'scopeConfig' => $this->scopeConfigMock,
                'braintreeConfiguration' => $this->braintreeConfigurationMock,
                'braintreeClientToken' => $this->braintreeClientTokenMock,
                'sourceCountry' => $this->sourceCountryMock,
            ]
        );
    }

    public function testConstructorActive()
    {
        $prefix = 'payment/braintree/';

        $environment = \Magento\Braintree\Model\Source\Environment::ENVIRONMENT_PRODUCTION;
        $merchantId = 'merchantId';
        $publicKey = 'public_key';
        $privateKey = 'private_key';
        $merchantAccountId = 'merchantAccountId';
        $clientToken = 'clientToken';

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with($prefix . Config::KEY_ACTIVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn(1);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with($prefix . Config::KEY_ENVIRONMENT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn($environment);
        $this->scopeConfigMock->expects($this->at(2))
            ->method('getValue')
            ->with($prefix . Config::KEY_MERCHANT_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn($merchantId);
        $this->scopeConfigMock->expects($this->at(3))
            ->method('getValue')
            ->with($prefix . Config::KEY_PUBLIC_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn($publicKey);
        $this->scopeConfigMock->expects($this->at(4))
            ->method('getValue')
            ->with($prefix . Config::KEY_PRIVATE_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn($privateKey);
        $this->scopeConfigMock->expects($this->at(5))
            ->method('getValue')
            ->with($prefix . Config::KEY_MERCHANT_ACCOUNT_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn($merchantAccountId);


        $this->braintreeConfigurationMock->expects($this->once())
            ->method('environment')
            ->with($environment);
        $this->braintreeConfigurationMock->expects($this->once())
            ->method('merchantId')
            ->with($merchantId);
        $this->braintreeConfigurationMock->expects($this->once())
            ->method('publicKey')
            ->with($publicKey);
        $this->braintreeConfigurationMock->expects($this->once())
            ->method('privateKey')
            ->with($privateKey);

        $this->model = new Config(
            $this->scopeConfigMock,
            $this->braintreeConfigurationMock,
            $this->braintreeClientTokenMock,
            $this->sourceCountryMock
        );

        $this->assertEquals($merchantAccountId, $this->model->getMerchantAccountId());
        $this->braintreeClientTokenMock->expects($this->once())
            ->method('generate')
            ->willReturn($clientToken);
        $this->assertEquals($clientToken, $this->model->getClientToken());
        //second call will return cached version
        $this->assertEquals($clientToken, $this->model->getClientToken());
    }

    public function testConstructorInActive()
    {
        $prefix = 'payment/braintree/';

        $this->scopeConfigMock->expects($this->once(0))
            ->method('getValue')
            ->with($prefix . Config::KEY_ACTIVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn(0);

        $this->model = new Config(
            $this->scopeConfigMock,
            $this->braintreeConfigurationMock,
            $this->braintreeClientTokenMock,
            $this->sourceCountryMock
        );

        $this->assertEquals('', $this->model->getMerchantAccountId());
    }

    public function testInitEnvironment()
    {
        $prefix = 'payment/braintree/';
        $storeId  = 2;

        $environment2 = \Magento\Braintree\Model\Source\Environment::ENVIRONMENT_SANDBOX;
        $merchantId2 = 'merchantId_2';
        $publicKey2 = 'public_key_2';
        $privateKey2 = 'private_key_2';
        $merchantAccountId2 = 'merchantAccountId_2';
        $clientToken2 = 'clientToken_2';

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with($prefix . Config::KEY_ACTIVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn(1);

        //for the initEnvironment call
        $this->scopeConfigMock->expects($this->at(6))
            ->method('getValue')
            ->with($prefix . Config::KEY_ENVIRONMENT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($environment2);
        $this->scopeConfigMock->expects($this->at(7))
            ->method('getValue')
            ->with($prefix . Config::KEY_MERCHANT_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($merchantId2);
        $this->scopeConfigMock->expects($this->at(8))
            ->method('getValue')
            ->with($prefix . Config::KEY_PUBLIC_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($publicKey2);
        $this->scopeConfigMock->expects($this->at(9))
            ->method('getValue')
            ->with($prefix . Config::KEY_PRIVATE_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($privateKey2);
        $this->scopeConfigMock->expects($this->at(10))
            ->method('getValue')
            ->with(
                $prefix . Config::KEY_MERCHANT_ACCOUNT_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )->willReturn($merchantAccountId2);


        //for the initEnvironment call
        $this->braintreeConfigurationMock->expects($this->at(4))
            ->method('environment')
            ->with($environment2);
        $this->braintreeConfigurationMock->expects($this->at(5))
            ->method('merchantId')
            ->with($merchantId2);
        $this->braintreeConfigurationMock->expects($this->at(6))
            ->method('publicKey')
            ->with($publicKey2);
        $this->braintreeConfigurationMock->expects($this->at(7))
            ->method('privateKey')
            ->with($privateKey2);

        $this->model = new Config(
            $this->scopeConfigMock,
            $this->braintreeConfigurationMock,
            $this->braintreeClientTokenMock,
            $this->sourceCountryMock
        );

        $this->model->initEnvironment($storeId);
        $this->assertEquals($merchantAccountId2, $this->model->getMerchantAccountId());
        $this->braintreeClientTokenMock->expects($this->once())
            ->method('generate')
            ->willReturn($clientToken2);
        $this->assertEquals($clientToken2, $this->model->getClientToken());
        //second call will return cached version
        $this->assertEquals($clientToken2, $this->model->getClientToken());
    }

    /**
     * @dataProvider canUseForCountryAllowSpecificDataProvider
     */
    public function testCanUseForCountryAllowSpecific($countryId, $expected)
    {
        $prefix = 'payment/braintree/';
        $specificCountry = 'FR,US';

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with($prefix . Config::KEY_ALLOW_SPECIFIC, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn(1);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with($prefix . Config::KEY_SPECIFIC_COUNTRY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn($specificCountry);
        $this->assertEquals($expected, $this->model->canUseForCountry($countryId));
    }

    public function canUseForCountryAllowSpecificDataProvider()
    {
        return [
            'US' => [
                'country' => 'US',
                'expected' => true,
            ],
            'TT' => [
                'country' => 'TT',
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider canUseForCountryNotAllowSpecificDataProvider
     */
    public function testCanUseForCountryNotAllowSpecific($isRestricted, $expected)
    {
        $countryId  = 'non-existing';
        $prefix = 'payment/braintree/';

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with($prefix . Config::KEY_ALLOW_SPECIFIC, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn(0);
        $this->sourceCountryMock->expects($this->once())
            ->method('isCountryRestricted')
            ->with($countryId)
            ->willReturn($isRestricted);
        $this->assertEquals($expected, $this->model->canUseForCountry($countryId));
    }

    public function canUseForCountryNotAllowSpecificDataProvider()
    {
        return [
            'restricted' => [
                'is_restricted' => true,
                'expected' => false,
            ],
            'non_restricted' => [
                'is_restricted' => false,
                'expected' => true,
            ],
        ];
    }
}
