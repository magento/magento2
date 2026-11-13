<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Backend\Model\Session\Quote;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\MessageInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Service\OrderService;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\TestCase\AbstractBackendController;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Class test backend order save.
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends AbstractBackendController
{
    /**
     * @var TransportBuilderMock
     */
    private $transportBuilder;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var string
     */
    protected $resource = 'Magento_Sales::create';

    /**
     * @var string
     */
    protected $uri = 'backend/sales/order_create/save';

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->transportBuilder = $this->_objectManager->get(TransportBuilderMock::class);
        $this->formKey = $this->_objectManager->get(FormKey::class);
    }

    /**
     * Checks a case when order creation is failed on payment method processing but new customer already created
     * in the database and after new controller dispatching the customer should be already loaded in session
     * to prevent invalid validation.
     *
     * @magentoDataFixture Magento/Sales/_files/quote_with_new_customer.php
     */
    public function testExecuteWithPaymentOperation()
    {
        /** @var OrderService|MockObject $orderService */
        $orderService = $this->getMockBuilder(OrderService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderService->method('place')
            ->willThrowException(new LocalizedException(__('Transaction has been declined.')));
        $this->_objectManager->addSharedInstance($orderService, OrderService::class);

        $quote = $this->getQuote('2000000001');
        $session = $this->_objectManager->get(Quote::class);
        $session->setQuoteId($quote->getId());
        $session->setCustomerId(0);

        $email = 'john.doe001@test.com';
        $data = [
            'account' => [
                'email' => $email,
            ]
        ];
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->getRequest()->setPostValue(['order' => $data]);

        $this->dispatch('backend/sales/order_create/save');
        $this->assertSessionMessages(
            self::equalTo(['Transaction has been declined.']),
            MessageInterface::TYPE_ERROR
        );

        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->_objectManager->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get($email);

        self::assertNotEmpty($session->getCustomerId());
        self::assertEquals($customer->getId(), $session->getCustomerId());

        $this->_objectManager->removeSharedInstance(OrderService::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     *
     * @return void
     */
    public function testSendEmailOnOrderSave(): void
    {
        $this->prepareRequest(['send_confirmation' => true]);
        $this->dispatch('backend/sales/order_create/save');
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You created the order.')]),
            MessageInterface::TYPE_SUCCESS
        );

        $this->assertRedirect($this->stringContains('sales/order/view/'));

        $orderId = $this->getOrderId();
        if ($orderId === false) {
            $this->fail('Order is not created.');
        }
        $order = $this->getOrder($orderId);

        $message = $this->transportBuilder->getSentMessage();
        $subject = __('Your %1 order confirmation', $order->getStore()->getFrontendName())->render();
        $assert = $this->logicalAnd(
            new StringContains($order->getBillingAddress()->getName()),
            new StringContains(
                'Thank you for your order from ' . $order->getStore()->getFrontendName()
            ),
            new StringContains(
                "Your Order <span class=\"no-link\">#{$order->getIncrementId()}</span>"
            )
        );

        $this->assertEquals($message->getSubject(), $subject);
        $this->assertThat(quoted_printable_decode($message->getBody()->bodyToString()), $assert);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     *
     * @return void
     */
    public function testNotSendEmailOnOrderSave(): void
    {
        $this->prepareRequest();
        $this->dispatch('backend/sales/order_create/save');
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You created the order.')]),
            MessageInterface::TYPE_SUCCESS
        );

        $this->assertRedirect($this->stringContains('sales/order/view/'));

        $orderId = $this->getOrderId();
        if ($orderId === false) {
            $this->fail('Order is not created.');
        }

        $this->assertNull($this->transportBuilder->getSentMessage());
    }

    /**
     * A form rendered for a new order must not cancel the order another tab opened for editing
     */
    #[
        DbIsolation(true),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testSubmitOfFormRenderedForAnotherOrderIsRejected(): void
    {
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $session = $this->_objectManager->get(Quote::class);
        $session->setOrderId($order->getEntityId());

        $this->getRequest()
            ->setMethod(Http::METHOD_POST)
            ->setParams(['form_key' => $this->formKey->getFormKey()])
            ->setPostValue(['edited_order_id' => '']);
        $this->dispatch('backend/sales/order_create/save');

        $this->assertSessionMessages(
            $this->equalTo([
                (string)__(
                    'The order form is outdated because another order was opened for editing in a different tab. '
                    . 'Please reload the page.'
                )
            ]),
            MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect($this->stringContains('sales/order_create/'));
        $this->assertEquals($order->getEntityId(), $session->getOrderId());

        $editedOrder = $this->getOrder((int)$order->getEntityId());
        $this->assertNotEquals(Order::STATE_CANCELED, $editedOrder->getState());
        $this->assertEmpty($editedOrder->getRelationChildId());
    }

    /**
     * A form rendered for the order held in the session is saved as before
     */
    #[
        DbIsolation(true),
        DataFixture('Magento/Sales/_files/guest_quote_with_addresses.php'),
    ]
    public function testSubmitOfFormRenderedForCurrentSessionOrderIsAccepted(): void
    {
        $this->prepareRequest();
        $this->getRequest()->setPostValue('edited_order_id', '');
        $this->dispatch('backend/sales/order_create/save');

        $this->assertSessionMessages(
            $this->equalTo([(string)__('You created the order.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('sales/order/view/'));
    }

    /**
     * Gets quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    private function getQuote(string $reservedOrderId): \Magento\Quote\Api\Data\CartInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)->getItems();

        return array_pop($items);
    }

    /**
     * @inheritdoc
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     */
    public function testAclHasAccess()
    {
        $this->prepareRequest();

        parent::testAclHasAccess();
    }

    /**
     * @inheritdoc
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     */
    public function testAclNoAccess()
    {
        $this->prepareRequest();

        parent::testAclNoAccess();
    }

    /**
     * @param int $orderId
     * @return OrderInterface
     */
    private function getOrder(int $orderId): OrderInterface
    {
        return $this->_objectManager->get(OrderRepository::class)->get($orderId);
    }

    /**
     * @param array $params
     * @return void
     */
    private function prepareRequest(array $params = []): void
    {
        $quote = $this->getQuote('guest_quote');
        $session = $this->_objectManager->get(Quote::class);
        $session->setQuoteId($quote->getId());
        $session->setCustomerId(0);

        $email = 'john.doe001@test.com';
        $data = [
            'account' => [
                'email' => $email,
            ],
        ];

        $data = array_replace_recursive($data, $params);

        $this->getRequest()
            ->setMethod('POST')
            ->setParams(['form_key' => $this->formKey->getFormKey()])
            ->setPostValue(['order' => $data]);
    }

    /**
     * @return string|bool
     */
    protected function getOrderId()
    {
        $currentUrl = $this->getResponse()->getHeader('Location');
        $orderId = false;

        if (preg_match('/order_id\/(?<order_id>\d+)/', $currentUrl, $matches)) {
            $orderId = $matches['order_id'] ?? '';
        }

        return $orderId;
    }
}
