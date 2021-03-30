<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Controller\Checkout;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\MessageInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepository;
use Magento\TestFramework\TestCase\AbstractController;
use Psr\Log\LoggerInterface;

/**
 * Test class for @see \Magento\Multishipping\Controller\Checkout\AddressesPost.
 *
 * @magentoAppArea frontend
 */
class AddressesPostTest extends AbstractController
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->quoteRepository = $this->_objectManager->get(QuoteRepository::class);
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address.php
     */
    public function testExecute()
    {
        $quote = $this->getQuote('test_order_1');
        $this->setMultiShippingToQuote($quote);
        $quoteItems = $quote->getItems();
        $quoteItemId = array_shift($quoteItems)->getItemId();
        $this->loginCustomer();

        $qty = 3;
        $productPrice = 10;
        $request = [
            'ship' => [
                1 => [
                    $quoteItemId => [
                        'qty' => $qty,
                        'address' => 1,
                    ],
                ],
            ]
        ];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($request);
        $this->dispatch('multishipping/checkout/addressesPost');
        $freshQuote = $this->getQuote('test_order_1');

        $this->assertEquals($qty, $freshQuote->getItemsQty());
        $this->assertEquals($productPrice * $qty, $freshQuote->getGrandTotal());
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address.php
     */
    public function testExecuteFail()
    {
        $msg = 'Verify the shipping address information and continue.';

        $quote = $this->getQuote('test_order_1');
        $this->setMultiShippingToQuote($quote);
        $quoteItems = $quote->getItems();
        $quoteItemId = array_shift($quoteItems)->getItemId();
        $this->loginCustomer();
        $request = [
            'ship' => [
                1 => [
                    $quoteItemId => [
                        'qty' => 1,
                        'address' => $quoteItemId,
                    ],
                ],
            ],
        ];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($request);
        $this->dispatch('multishipping/checkout/addressesPost');

        $this->assertSessionMessages($this->equalTo([$msg]), MessageInterface::TYPE_ERROR);
    }

    /**
     * @param CartInterface $quote
     * @return void
     */
    private function setMultiShippingToQuote(CartInterface $quote): void
    {
        $quote->setIsMultiShipping(1);
        $this->quoteRepository->save($quote);
    }

    /**
     * Authenticates customer and creates customer session.
     *
     * @return void
     */
    private function loginCustomer(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        /** @var AccountManagementInterface $service */
        $service = $this->_objectManager->create(AccountManagementInterface::class);
        try {
            $customer = $service->authenticate('customer@example.com', 'password');
        } catch (LocalizedException $e) {
            $this->fail($e->getMessage());
        }
        /** @var CustomerSession $customerSession */
        $customerSession = $this->_objectManager->get(CustomerSession::class, [$logger]);
        $customerSession->setCustomerDataAsLoggedIn($customer);
    }

    /**
     * Gets quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return CartInterface
     */
    private function getQuote(string $reservedOrderId): CartInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->create(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)->create();
        /** @var QuoteRepository $quoteRepository */
        $quoteRepository = $this->_objectManager->get(QuoteRepository::class);
        $items = $quoteRepository->getList($searchCriteria)->getItems();

        return array_pop($items);
    }
}
