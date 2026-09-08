<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\AdminOrder;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for admin order edit with fully consumed product stock.
 *
 * @see https://github.com/magento/magento2/issues/39898
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AppArea('adminhtml')]
#[AppIsolation(true)]
#[DbIsolation(false)]
class CreateOrderEditStockTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var EmailSender|MockObject
     */
    private EmailSender|MockObject $emailSenderMock;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->messageManager = $this->objectManager->get(ManagerInterface::class);
        $this->emailSenderMock = $this->getMockBuilder(EmailSender::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Verify that editing an order which consumed all available stock
     * does not fail with "Product is out of stock" error.
     */
    #[
        DataFixture(ProductFixture::class, ['sku' => 'test-stock-product', 'price' => 10], 'product'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 5, 'is_in_stock' => 1]),
        DataFixture(CustomerFixture::class, ['email' => 'customer@example.com'], 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 5]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testInitFromOrderSucceedsWhenProductStockIsFullyConsumed(): void
    {
        $fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $order = $fixtures->get('order');

        /** @var Create $model */
        $model = $this->objectManager->create(
            Create::class,
            ['messageManager' => $this->messageManager, 'emailSender' => $this->emailSenderMock]
        );

        // Clear any pre-existing messages
        $this->messageManager->getMessages(true);
        $this->objectManager->get(Registry::class)->unregister('rule_data');

        $model->initFromOrder($order);

        self::assertGreaterThan(
            0,
            $model->getQuote()->getItemsCollection()->count(),
            'Order edit quote must contain items even when product stock is fully consumed'
        );
        self::assertSame(
            0,
            $this->messageManager->getMessages()->getCount(),
            'No error messages should be generated when editing an order with consumed stock'
        );
    }
}
