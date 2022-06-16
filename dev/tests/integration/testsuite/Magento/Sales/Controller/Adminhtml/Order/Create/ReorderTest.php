<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\TestFramework\Helper\Xpath;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test for reorder controller.
 *
 * @see \Magento\Sales\Controller\Adminhtml\Order\Create\Reorder
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReorderTest extends AbstractBackendController
{
    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /** @var CartInterface */
    private $quote;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var array
     */
    private $customerIds = [];

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->orderFactory = $this->_objectManager->get(OrderInterfaceFactory::class);
        $this->quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $this->orderRepository = $this->_objectManager->get(OrderRepositoryInterface::class);
        $this->customerFactory = $this->_objectManager->get(CustomerInterfaceFactory::class);
        $this->accountManagement = $this->_objectManager->get(AccountManagementInterface::class);
        $this->customerRepository = $this->_objectManager->get(CustomerRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->quote instanceof CartInterface) {
            $this->quoteRepository->delete($this->quote);
        }
        foreach ($this->customerIds as $customerId) {
            try {
                $this->customerRepository->deleteById($customerId);
            } catch (NoSuchEntityException $e) {
                //customer already deleted
            }
        }
        parent::tearDown();
    }

    /**
     * Reorder with JS calendar options
     *
     * @magentoConfigFixture current_store catalog/custom_options/use_calendar 1
     * @magentoDataFixture Magento/Sales/_files/order_with_date_time_option_product.php
     *
     * @return void
     */
    public function testReorderAfterJSCalendarEnabled(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->dispatchReorderRequest((int)$order->getId());
        $this->assertRedirect($this->stringContains('backend/sales/order_create'));
        $this->quote = $this->getQuote('customer@example.com');
        $this->assertTrue(!empty($this->quote));
    }

    /**
     * Test load billing address by reorder for delegating customer
     *
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_address.php
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testLoadBillingAddressAfterReorderWithDelegatingCustomer(): void
    {
        $orderId = $this->getOrderWithDelegatingCustomer()->getId();
        $this->getRequest()->setMethod(Http::METHOD_GET);
        $this->getRequest()->setParam('order_id', $orderId);
        $this->dispatch('backend/sales/order_create/loadBlock/block/billing_address');
        $html = $this->getResponse()->getBody();
        $this->assertEquals(
            0,
            Xpath::getElementsCountForXpath(
                '//*[@id="order-billing_address_save_in_address_book" and contains(@checked, "checked")]',
                $html
            ),
            'Billing address checked "Save in address book"'
        );
    }

    /**
     * Get Order with delegating customer
     *
     * @return OrderInterface
     */
    private function getOrderWithDelegatingCustomer(): OrderInterface
    {
        $orderAutoincrementId = '100000001';
        /** @var Order $orderModel */
        $orderModel = $this->orderFactory->create();
        $orderModel->loadByIncrementId($orderAutoincrementId);
        //Saving new customer with prepared data from order.
        /** @var CustomerInterface $customer */
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId(1)
            ->setEmail('customer_order_delegate@example.com')
            ->setGroupId(1)
            ->setStoreId(1)
            ->setPrefix('Mr.')
            ->setFirstname('John')
            ->setMiddlename('A')
            ->setLastname('Smith')
            ->setSuffix('Esq.')
            ->setTaxvat('12')
            ->setGender(0);
        $createdCustomer = $this->accountManagement->createAccount(
            $customer,
            '12345abcD'
        );
        $this->customerIds[] = $createdCustomer->getId();
        $orderModel->setCustomerId($createdCustomer->getId());

        return $this->orderRepository->save($orderModel);
    }

    /**
     * Dispatch reorder request.
     *
     * @param null|int $orderId
     * @return void
     */
    private function dispatchReorderRequest(?int $orderId = null): void
    {
        $this->getRequest()->setMethod(Request::METHOD_GET);
        $this->getRequest()->setParam('order_id', $orderId);
        $this->dispatch('backend/sales/order_create/reorder');
    }

    /**
     * Gets quote by reserved order id.
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    private function getQuote(string $customerEmail): \Magento\Quote\Api\Data\CartInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('customer_email', $customerEmail)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)->getItems();

        return array_pop($items);
    }
}
