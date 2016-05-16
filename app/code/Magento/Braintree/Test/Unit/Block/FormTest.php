<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Block;

use Magento\Backend\Model\Session\Quote;
use Magento\Braintree\Block\Form;
use Magento\Braintree\Gateway\Config\Config as GatewayConfig;
use Magento\Braintree\Model\Adminhtml\Source\CcType;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\Config;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use Magento\Vault\Model\VaultPaymentInterface;

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
     * @var Form
     */
    private $block;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionQuote;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $gatewayConfig;

    /**
     * @var CcType|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ccType;

    /**
     * @var VaultPaymentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $vaultService;

    protected function setUp()
    {
        $this->initCcTypeMock();
        $this->initSessionQuoteMock();
        $this->initGatewayConfigMock();

        $this->vaultService = $this->getMock(VaultPaymentInterface::class);

        $managerHelper = new ObjectManager($this);
        $this->block = $managerHelper->getObject(Form::class, [
            'paymentConfig' => $managerHelper->getObject(Config::class),
            'sessionQuote' => $this->sessionQuote,
            'gatewayConfig' => $this->gatewayConfig,
            'ccType' => $this->ccType,
            'vaultService' => $this->vaultService
        ]);
    }

    /**
     * @covers \Magento\Braintree\Block\Form::getCcAvailableTypes
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

    public function testIsVaultEnabled()
    {
        $this->vaultService->expects(static::once())
            ->method('isActiveForPayment')
            ->with(ConfigProvider::CODE)
            ->willReturn(true);

        static::assertTrue($this->block->isVaultEnabled());
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

        $this->ccType->expects(static::any())
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

        $this->sessionQuote->expects(static::any())
            ->method('getQuote')
            ->willReturnSelf();
        $this->sessionQuote->expects(static::any())
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
