<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Address as AddressType;
use Magento\Sales\Model\OrderFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks order address edit block
 *
 * @see \Magento\Sales\Block\Adminhtml\Order\Address
 *
 * @magentoAppArea adminhtml
 */
class AddressTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Address */
    private $block;

    /** @var Registry */
    private $registry;

    /** @var OrderFactory */
    private $orderFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Address::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->orderFactory = $this->objectManager->get(OrderFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('order_address');

        parent::tearDown();
    }

    /**
     * @dataProvider addressTypeProvider
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @param string $type
     * @return void
     */
    public function testGetHeaderText(string $type): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId(100000001);
        $address = $this->getAddressByType($order, $type);
        $this->registry->unregister('order_address');
        $this->registry->register('order_address', $address);
        $text = $this->block->getHeaderText();
        $this->assertEquals(
            (string)__('Edit Order %1 %2 Address', $order->getIncrementId(), ucfirst($type)),
            (string)$text
        );
    }

    /**
     * @return array
     */
    public function addressTypeProvider(): array
    {
        return [
            'billing_address' => [
                AddressType::TYPE_BILLING,
            ],
            'shipping_address' => [
                AddressType::TYPE_SHIPPING,
            ]
        ];
    }

    /**
     * Get address by address type
     *
     * @param OrderInterface $order
     * @param string $type
     * @return OrderAddressInterface|null
     */
    private function getAddressByType(OrderInterface $order, string $type): ?OrderAddressInterface
    {
        return $type ===  AddressType::TYPE_BILLING ? $order->getBillingAddress() : $order->getShippingAddress();
    }
}
