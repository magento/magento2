<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Test\Fixture\Creditmemo as CreditmemoFixture;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Test\Fixture\Rule as RuleFixture;
use Magento\TestFramework\Fixture\Config as Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var TransportBuilderMock
     */
    private $transportBuilderMock;

    /**
     * @var MutableScopeConfigInterface
     */
    private $mutableScopeConfig;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var EmailSenderHandler
     */
    private $emailSenderHandler;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $objectManager = Bootstrap::getObjectManager();
        $this->collectionFactory = $objectManager->get(CollectionFactory::class);
        $this->transportBuilderMock = $objectManager->get(TransportBuilderMock::class);
        $this->mutableScopeConfig = $objectManager->get(MutableScopeConfigInterface::class);
        $this->emailSenderHandler = Bootstrap::getObjectManager()->create(
            EmailSenderHandler::class,
            [
                'emailSender'       => $objectManager->get(OrderSender::class),
                'entityResource'    => $objectManager->get(\Magento\Sales\Model\ResourceModel\Order::class),
                'entityCollection'  => $this->collectionFactory->create(),
                'identityContainer' => $objectManager->create(OrderIdentity::class),
            ]
        );
        $this->transportBuilderMock->clean();
    }

    protected function tearDown(): void
    {
        $this->transportBuilderMock->clean();
        parent::tearDown();
    }

    /**
     * Tests that multiple credit memos can be created for zero total order if not all items are refunded yet
     */
    #[
        Config('carriers/freeshipping/active', '1', 'store', 'default'),
        Config('payment/free/active', '1', 'store', 'default'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::BY_PERCENT_ACTION,
                'discount_amount' => 100,
                'apply_to_shipping' => 0,
                'stop_rules_processing' => 0,
                'sort_order' => 1,
            ]
        ),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            SetDeliveryMethodFixture::class,
            ['cart_id' => '$cart.id$', 'carrier_code' => 'freeshipping', 'method_code' => 'freeshipping']
        ),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$', 'method' => 'free']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(
            CreditmemoFixture::class,
            ['order_id' => '$order.id$', 'items' => [['qty' => 1, 'product_id' => '$product.id$']]],
            'creditmemo'
        ),
    ]
    public function testMultipleCreditmemosForZeroTotalOrder()
    {
        $order = $this->fixtures->get('order');
        $this->assertEquals(0, $order->getGrandTotal());
        $order->unsetData('forced_can_creditmemo');
        $this->assertTrue(
            $order->canCreditmemo(),
            'Should be possible to create second credit memo for zero total order if not all items are refunded yet'
        );
    }

    #[
        Config('system/smtp/disable', '1', 'store', 'default'),
        Config('sales_email/general/async_sending', '1'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order')
    ]
    public function testAsyncEmailForOrderCreatedWhenEmailSendingWasDisabled(): void
    {
        $isEmailSent = false;
        $this->transportBuilderMock->setOnMessageSentCallback(
            function () use (&$isEmailSent) {
                $isEmailSent = true;
            }
        );
        $order = $this->fixtures->get('order');
        $this->assertEquals(0, $order->getSendEmail());
        $this->assertNull($order->getEmailSent());
        $this->mutableScopeConfig->setValue('system/smtp/disable', 0, 'store', 'default');
        $this->emailSenderHandler->sendEmails();
        $this->assertFalse(
            $isEmailSent,
            'Email is not expected to be sent'
        );
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('entity_id', $order->getId());
        $order = $collection->getFirstItem();
        $this->assertEquals(0, $order->getSendEmail());
        $this->assertNull($order->getEmailSent());
    }
}
