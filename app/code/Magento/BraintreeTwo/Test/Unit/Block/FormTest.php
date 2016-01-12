<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Block;

use Magento\Backend\Model\Session\Quote;
use Magento\BraintreeTwo\Block\Form;
use Magento\BraintreeTwo\Gateway\Config\Config as GatewayConfig;
use Magento\BraintreeTwo\Model\Adminhtml\Source\CcType;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\Config;

/**
 * Class FormTest
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    public static $baseCardTypes = [
        'AE' => 'American Express',
        'VI' => 'Visa',
        'MC' => 'MasterCard',
        'DI' => 'Discover',
        'JBC' => 'JBC',
        'CUP' => 'China Union Pay',
        'MI' => 'Maestro',
    ];

    public static $configCardTypes = [
        'AE', 'VI', 'MC', 'DI', 'JBC'
    ];

    /**
     * @var \Magento\BraintreeTwo\Block\Form
     */
    private $block;

    /**
     * @var \Magento\Backend\Model\Session\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionQuote;

    /**
     * @var \Magento\BraintreeTwo\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $gatewayConfig;

    /**
     * @var \Magento\BraintreeTwo\Model\Adminhtml\Source\CcType|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ccType;


    protected function setUp()
    {
        $this->initCcTypeMock();
        $this->initSessionQuoteMock();
        $this->initGatewayConfigMock();

        $managerHelper = new ObjectManager($this);
        $this->block = $managerHelper->getObject(Form::class, [
            'paymentConfig' => $managerHelper->getObject(Config::class),
            'sessionQuote' => $this->sessionQuote,
            'gatewayConfig' => $this->gatewayConfig,
            'ccType' => $this->ccType
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
            ->method('getAvailableCardTypes')
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
     * Create mock for credit card type
     */
    private function initCcTypeMock()
    {
        $this->ccType = $this->getMockBuilder(CcType::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCcTypeLabelMap'])
            ->getMock();

        $this->ccType->expects(static::once())
            ->method('getCcTypeLabelMap')
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
            ->setMethods(['getCountryAvailableCardTypes', 'getAvailableCardTypes'])
            ->getMock();
    }
}
