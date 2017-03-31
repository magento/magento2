<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Block;

use Magento\Backend\Model\Session\Quote;
use Magento\Braintree\Block\Form;
use Magento\Braintree\Gateway\Config\Config as GatewayConfig;
use Magento\Braintree\Model\Adminhtml\Source\CcType;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Config;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Model\VaultPaymentInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

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
     * @var Quote|MockObject
     */
    private $sessionQuote;

    /**
     * @var Config|MockObject
     */
    private $gatewayConfig;

    /**
     * @var CcType|MockObject
     */
    private $ccType;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Data|MockObject
     */
    private $paymentDataHelper;

    protected function setUp()
    {
        $this->initCcTypeMock();
        $this->initSessionQuoteMock();
        $this->initGatewayConfigMock();
        
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->paymentDataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodInstance'])
            ->getMock();

        $managerHelper = new ObjectManager($this);
        $this->block = $managerHelper->getObject(Form::class, [
            'paymentConfig' => $managerHelper->getObject(Config::class),
            'sessionQuote' => $this->sessionQuote,
            'gatewayConfig' => $this->gatewayConfig,
            'ccType' => $this->ccType,
            'storeManager' => $this->storeManager
        ]);

        $managerHelper->setBackwardCompatibleProperty($this->block, 'paymentDataHelper', $this->paymentDataHelper);
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

    /**
     * @covers \Magento\Braintree\Block\Form::isVaultEnabled
     */
    public function testIsVaultEnabled()
    {
        $storeId = 1;
        $store = $this->getMockForAbstractClass(StoreInterface::class);
        $this->storeManager->expects(static::once())
            ->method('getStore')
            ->willReturn($store);
        
        $store->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);

        $vaultPayment = $this->getMockForAbstractClass(VaultPaymentInterface::class);
        $this->paymentDataHelper->expects(static::once())
            ->method('getMethodInstance')
            ->with(ConfigProvider::CC_VAULT_CODE)
            ->willReturn($vaultPayment);

        $vaultPayment->expects(static::once())
            ->method('isActive')
            ->with($storeId)
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
