<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Class checks customer's shopping cart controller.
 *
 * @see \Magento\Customer\Controller\Adminhtml\Index\Cart
 * @magentoAppArea adminhtml
 */
class CartTest extends AbstractBackendController
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerRepository = $this->_objectManager->get(CustomerRepositoryInterface::class);
        $this->quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     * @return void
     */
    public function testCartAction(): void
    {
        $this->dispatchShoppingCart(
            [
                'id' => 1,
                'website_id' => 1,
            ],
            ['delete' => 1]
        );
        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('<div id="customer_cart_grid"', $body);
    }

    /**
     * Delete customer shopping cart item
     *
     * @magentoDataFixture Magento/Checkout/_files/customer_quote_with_items_simple_product_options.php
     * @return void
     */
    public function testDeleteCartItem(): void
    {
        $customer = $this->customerRepository->get('customer_uk_address@test.com');
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getForCustomer($customer->getId());
        $quoteItemId = $quote->getItemsCollection()->getFirstItem()->getItemId();
        $this->assertNotEmpty($quoteItemId);
        $this->dispatchShoppingCart(
            [
                'id' => $customer->getId(),
                'website_id' => $customer->getWebsiteId(),
            ],
            ['delete' => $quoteItemId]
        );
        $quote->getItemsCollection(false);
        $this->assertFalse(
            $quote->getItemById($quoteItemId),
            sprintf('Customer\'s shopping cart item with ID = %s has not been deleted', $quoteItemId)
        );
    }

    /**
     * Dispatch admin shopping cart using backend/customer/index/cart action.
     *
     * @param array $params
     * @param array $postValue
     * @return void
     */
    private function dispatchShoppingCart(array $params = [], array $postValue = []): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParams($params);
        $this->getRequest()->setPostValue($postValue);
        $this->dispatch('backend/customer/index/cart');
    }
}
