<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\MessageInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Service\OrderService;
use Magento\TestFramework\TestCase\AbstractBackendController;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class SaveTest extends AbstractBackendController
{
    /**
     * Checks a case when order creation is failed on payment method processing but new customer already created
     * in the database and after new controller dispatching the customer should be already loaded in session
     * to prevent invalid validation.
     *
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Sales/_files/quote_with_new_customer.php
     */
    public function testExecuteWithPaymentOperation()
    {
        $quote = $this->getQuote('2000000001');
        $session = $this->_objectManager->get(Quote::class);
        $session->setQuoteId($quote->getId());
        $session->setCustomerId(0);

        $email = 'john.doe001@test.com';
        $data = [
            'account' => [
                'email' => $email
            ]
        ];
        $this->getRequest()->setPostValue(['order' => $data]);

        /** @var OrderService|MockObject $orderService */
        $orderService = $this->getMockBuilder(OrderService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderService->method('place')
            ->willThrowException(new LocalizedException(__('Transaction has been declined.')));
        $this->_objectManager->addSharedInstance($orderService, OrderService::class);

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
     * Gets quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    private function getQuote($reservedOrderId)
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
}
