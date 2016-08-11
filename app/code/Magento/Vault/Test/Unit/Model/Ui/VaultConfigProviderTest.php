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
use Magento\Vault\Model\Ui\VaultConfigProvider;
use Magento\Vault\Model\VaultPaymentInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class VaultConfigProviderTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Data|MockObject
     */
    private $paymentDataHelper;

    /**
     * @var VaultPaymentInterface|MockObject
     */
    private $vaultPayment;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var StoreInterface|MockObject
     */
    private $store;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var VaultConfigProvider
     */
    private $vaultConfigProvider;

    protected function setUp()
    {
        $this->paymentDataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreMethods'])
            ->getMock();

        $this->vaultPayment = $this->getMockForAbstractClass(VaultPaymentInterface::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->store = $this->getMockForAbstractClass(StoreInterface::class);
        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->vaultConfigProvider = new VaultConfigProvider($this->storeManager, $this->session);
        $objectManager->setBackwardCompatibleProperty(
            $this->vaultConfigProvider,
            'paymentDataHelper',
            $this->paymentDataHelper
        );
    }

    /**
     * @param int $customerId
     * @param bool $vaultEnabled
     * @dataProvider customerIdProvider
     */
    public function testGetConfig($customerId, $vaultEnabled)
    {
        $storeId = 1;
        $vaultPaymentCode = 'vault_payment';

        $expectedConfiguration = [
            'vault' => [
                $vaultPaymentCode => [
                    'is_enabled' => $vaultEnabled
                ],
            ]
        ];

        $this->session->expects(static::once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->storeManager->expects(static::exactly(2))
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects(static::exactly(2))
            ->method('getId')
            ->willReturn($storeId);

        $this->paymentDataHelper->expects(static::once())
            ->method('getStoreMethods')
            ->with($storeId)
            ->willReturn([$this->vaultPayment]);

        $this->vaultPayment->expects(static::once())
            ->method('getCode')
            ->willReturn($vaultPaymentCode);
        $this->vaultPayment->expects($customerId !== null ? static::once() : static::never())
            ->method('isActive')
            ->with($storeId)
            ->willReturn($vaultEnabled);

        static::assertEquals($expectedConfiguration, $this->vaultConfigProvider->getConfig());
    }

    /**
     * @return array
     */
    public function customerIdProvider()
    {
        return [
            [
                'id' => 1,
                'vault_enabled' => true
            ],
            [
                'id' => null,
                'vault_enabled' => false
            ]
        ];
    }
}
