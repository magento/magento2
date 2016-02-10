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
use Magento\BraintreeTwo\Model\Ui\ConfigProvider;
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
     * @var VaultConfigProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $vaultConfigProvider;


    protected function setUp()
    {
        $this->initCcTypeMock();
        $this->initSessionQuoteMock();
        $this->initGatewayConfigMock();

        $this->vaultConfigProvider = $this->getMockBuilder(VaultConfigProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfig'])
            ->getMock();

        $managerHelper = new ObjectManager($this);
        $this->block = $managerHelper->getObject(Form::class, [
            'paymentConfig' => $managerHelper->getObject(Config::class),
            'sessionQuote' => $this->sessionQuote,
            'gatewayConfig' => $this->gatewayConfig,
            'ccType' => $this->ccType,
            'vaultConfigProvider' => $this->vaultConfigProvider
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
     * @param array $config
     * @param bool $expected
     * @covers \Magento\BraintreeTwo\Block\Form::isVaultEnabled
     * @dataProvider vaultConfigProvider
     */
    public function testIsVaultEnabled(array $config, $expected)
    {
        $this->vaultConfigProvider->expects(static::once())
            ->method('getConfig')
            ->willReturn([
                VaultPaymentInterface::CODE => $config
            ]);

        static::assertEquals($expected, $this->block->isVaultEnabled());
    }

    /**
     * Get variations to test vault config
     * @return array
     */
    public function vaultConfigProvider()
    {
        return [
            [
                'config' => [
                    'vault_provider_code' => ConfigProvider::CODE,
                    'is_enabled' => true,
                ],
                'expected' => true
            ],
            [
                'config' => [
                    'vault_provider_code' => ConfigProvider::CODE,
                    'is_enabled' => false,
                ],
                'expected' => false
            ],
            [
                'config' => [
                    'vault_provider_code' => 'test payment 1',
                    'is_enabled' => true,
                ],
                'expected' => false
            ],
            [
                'config' => [
                    'vault_provider_code' => 'test payment 2',
                    'is_enabled' => false,
                ],
                'expected' => false
            ],
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
