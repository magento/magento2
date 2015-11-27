<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Block;

use Magento\Backend\Model\Session\Quote;
use Magento\BraintreeTwo\Block\Form;
use Magento\BraintreeTwo\Gateway\Config\Config as GatewayConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\Config;

class FormTest extends \PHPUnit_Framework_TestCase
{
    public static $baseCardTypes = [
        'AE' => 'American Express',
        'VI' => 'Visa',
        'MC' => 'MasterCard',
        'DI' => 'Discover',
        'JBC' => 'JBC',
        'OT' => 'Other'
    ];

    public static $configCardTypes = [
        'AE', 'VI', 'MC', 'DI', 'JBC'
    ];

    /**
     * @var \Magento\BraintreeTwo\Block\Form
     */
    private $block;

    /**
     * @var \Magento\Payment\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentConfig;

    /**
     * @var \Magento\Backend\Model\Session\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionQuote;

    /**
     * @var \Magento\BraintreeTwo\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $gatewayConfig;

    protected function setUp()
    {
        $this->initPaymentConfigMock();
        $this->initSessionQuoteMock();
        $this->initGatewayConfigMock();

        $managerHelper = new ObjectManager($this);
        $this->block = $managerHelper->getObject(Form::class, [
            'paymentConfig' => $this->paymentConfig,
            'sessionQuote' => $this->sessionQuote,
            'gatewayConfig' => $this->gatewayConfig
        ]);
    }

    /**
     * @covers \Magento\BraintreeTwo\Block\Form::getCcAvailableTypes
     * @param string $countryId
     * @param array $availableTypes
     * @param array $expected
     * @dataProvider countryCardTypesDataProvider
     */
    public function testGetCcAvailableTypes($countryId, array $availableTypes, array $expected)
    {
        $this->sessionQuote->expects(static::once())
            ->method('getCountryId')
            ->willReturn($countryId);

        $this->gatewayConfig->expects(static::once())
            ->method('getCcAvailableCardTypes')
            ->willReturn(self::$configCardTypes);

        $this->gatewayConfig->expects(static::once())
            ->method('getCountryAvailableCardTypes')
            ->with($countryId)
            ->willReturn($availableTypes);

        $result = $this->block->getCcAvailableTypes();
        static::assertEquals($expected, array_values($result));
    }

    /**
     * Get country card types testing data
     * @return array
     */
    public function countryCardTypesDataProvider()
    {
        return [
            ['US', ['AE', 'VI'], ['American Express', 'Visa']],
            ['UK', ['VI'], ['Visa']],
            ['CA', ['MC'], ['MasterCard']],
            ['UA', [], ['American Express', 'Visa', 'MasterCard', 'Discover', 'JBC']]
        ];
    }

    /**
     * Create mock for payment config
     */
    private function initPaymentConfigMock()
    {
        $this->paymentConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCcTypes'])
            ->getMock();

        $this->paymentConfig->expects(static::once())
            ->method('getCcTypes')
            ->willReturn(self::$baseCardTypes);
    }

    /**
     * Create mock for session quote
     */
    private function initSessionQuoteMock()
    {
        $this->sessionQuote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', 'getBillingAddress', 'getCountryId', '__wakeup'])
            ->getMock();

        $this->sessionQuote->expects(static::once())
            ->method('getQuote')
            ->willReturnSelf();
        $this->sessionQuote->expects(static::once())
            ->method('getBillingAddress')
            ->willReturnSelf();
    }

    /**
     * Create mock for gateway config
     */
    private function initGatewayConfigMock()
    {
        $this->gatewayConfig = $this->getMockBuilder(GatewayConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountryAvailableCardTypes', 'getCcAvailableCardTypes'])
            ->getMock();
    }
}
