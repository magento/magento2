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
use Magento\Vault\Model\VaultPaymentInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests \Magento\Braintree\Block\Form.
 */
class FormTest extends \PHPUnit\Framework\TestCase
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
    private $sessionQuoteMock;

    /**
     * @var Config|MockObject
     */
    private $gatewayConfigMock;

    /**
     * @var CcType|MockObject
     */
    private $ccTypeMock;

    /**
     * @var Data|MockObject
     */
    private $paymentDataHelperMock;

    /**
     * @var string
     */
    private $storeId = '1';

    protected function setUp()
    {
        $this->initCcTypeMock();
        $this->initSessionQuoteMock();
        $this->initGatewayConfigMock();

        $this->paymentDataHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodInstance'])
            ->getMock();

        $managerHelper = new ObjectManager($this);
        $this->block = $managerHelper->getObject(Form::class, [
            'paymentConfig' => $managerHelper->getObject(Config::class),
            'sessionQuote' => $this->sessionQuoteMock,
            'gatewayConfig' => $this->gatewayConfigMock,
            'ccType' => $this->ccTypeMock,
            'paymentDataHelper' =>$this->paymentDataHelperMock,
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
        $this->sessionQuoteMock->expects(static::once())
            ->method('getCountryId')
            ->willReturn($countryId);

        $this->gatewayConfigMock->expects(static::once())
            ->method('getAvailableCardTypes')
            ->with($this->storeId)
            ->willReturn(self::$configCardTypes);

        $this->gatewayConfigMock->expects(static::once())
            ->method('getCountryAvailableCardTypes')
            ->with($countryId, $this->storeId)
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
            ['UA', [], ['American Express', 'Visa', 'MasterCard', 'Discover', 'JBC']],
        ];
    }

    /**
     * @covers \Magento\Braintree\Block\Form::isVaultEnabled
     */
    public function testIsVaultEnabled()
    {
        $vaultPayment = $this->getMockForAbstractClass(VaultPaymentInterface::class);
        $this->paymentDataHelperMock->expects(static::once())
            ->method('getMethodInstance')
            ->with(ConfigProvider::CC_VAULT_CODE)
            ->willReturn($vaultPayment);

        $vaultPayment->expects(static::once())
            ->method('isActive')
            ->with($this->storeId)
            ->willReturn(true);

        static::assertTrue($this->block->isVaultEnabled());
    }

    /**
     * Create mock for credit card type
     */
    private function initCcTypeMock()
    {
        $this->ccTypeMock = $this->getMockBuilder(CcType::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCcTypeLabelMap'])
            ->getMock();

        $this->ccTypeMock->expects(static::any())
            ->method('getCcTypeLabelMap')
            ->willReturn(self::$baseCardTypes);
    }

    /**
     * Create mock for session quote
     */
    private function initSessionQuoteMock()
    {
        $this->sessionQuoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', 'getBillingAddress', 'getCountryId', '__wakeup', 'getStoreId'])
            ->getMock();

        $this->sessionQuoteMock->expects(static::any())
            ->method('getQuote')
            ->willReturnSelf();
        $this->sessionQuoteMock->expects(static::any())
            ->method('getBillingAddress')
            ->willReturnSelf();
        $this->sessionQuoteMock->expects(static::any())
            ->method('getStoreId')
            ->willReturn($this->storeId);
    }

    /**
     * Create mock for gateway config
     */
    private function initGatewayConfigMock()
    {
        $this->gatewayConfigMock = $this->getMockBuilder(GatewayConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountryAvailableCardTypes', 'getAvailableCardTypes'])
            ->getMock();
    }
}
