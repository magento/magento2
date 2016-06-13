<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model\Ui;

use Magento\Customer\Model\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Helper\Data;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CustomerTokenManagement;
use Magento\Vault\Model\Ui\TokensConfigProvider;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\VaultPaymentInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ConfigProviderTest
 *
 * @see \Magento\Vault\Model\Ui\TokensConfigProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class TokensConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var VaultPaymentInterface|MockObject
     */
    private $vaultPayment;

    /**
     * @var StoreInterface|MockObject
     */
    private $store;

    /**
     * @var CustomerTokenManagement|MockObject
     */
    private $customerTokenManagement;

    /**
     * @var Data|MockObject
     */
    private $paymentDataHelper;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->vaultPayment = $this->getMock(VaultPaymentInterface::class);
        $this->storeManager = $this->getMock(StoreManagerInterface::class);
        $this->store = $this->getMock(StoreInterface::class);
        $this->paymentDataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreMethods'])
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->customerTokenManagement = $this->getMockBuilder(CustomerTokenManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetConfig()
    {
        $storeId = 1;
        $vaultProviderCode = 'vault_provider_code';

        $expectedConfig = [
            'payment' => [
                'vault' => [
                    $vaultProviderCode . '_item_' . '0' => [
                        'config' => ['token_code' => 'code'],
                        'component' => 'Vendor_Module/js/vault_component'
                    ]
                ]
            ]
        ];

        $token = $this->getMockForAbstractClass(PaymentTokenInterface::class);
        $tokenUiComponentProvider = $this->getMockForAbstractClass(TokenUiComponentProviderInterface::class);
        $tokenUiComponent = $this->getMockForAbstractClass(TokenUiComponentInterface::class);

        $this->storeManager->expects(static::once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);
        
        $this->paymentDataHelper->expects(static::once())
            ->method('getStoreMethods')
            ->with($storeId)
            ->willReturn([$this->vaultPayment]);
        
        $this->vaultPayment->expects(static::once())
            ->method('isActive')
            ->with($storeId)
            ->willReturn(true);
        $this->vaultPayment->expects(static::once())
            ->method('getProviderCode')
            ->willReturn($vaultProviderCode);

        $this->customerTokenManagement->expects(static::once())
            ->method('getCustomerSessionTokens')
            ->willReturn([$token]);
        
        $token->expects(static::once())
            ->method('getPaymentMethodCode')
            ->willReturn($vaultProviderCode);

        $tokenUiComponentProvider->expects(static::once())
            ->method('getComponentForToken')
            ->with($token)
            ->willReturn($tokenUiComponent);
        $tokenUiComponent->expects(static::once())
            ->method('getConfig')
            ->willReturn(['token_code' => 'code']);
        $tokenUiComponent->expects(static::once())
            ->method('getName')
            ->willReturn('Vendor_Module/js/vault_component');

        $configProvider = new TokensConfigProvider(
            $this->storeManager,
            $this->customerTokenManagement,
            [
                $vaultProviderCode => $tokenUiComponentProvider
            ]
        );

        $this->objectManager->setBackwardCompatibleProperty(
            $configProvider,
            'paymentDataHelper',
            $this->paymentDataHelper
        );

        static::assertEquals($expectedConfig, $configProvider->getConfig());
    }
}
