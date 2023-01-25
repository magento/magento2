<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rss\Controller\Feed;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Wishlist\Model\Wishlist;

/**
 * Test for \Magento\Rss\Controller\Feed\Index.
 */
class IndexTest extends AbstractBackendController
{
    private const RSS_NEW_PRODUCTS_PATH = 'rss/feed/index/type/new_products/';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Wishlist
     */
    private $wishlist;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->customerRepository = $this->_objectManager->get(CustomerRepositoryInterface::class);
        $this->wishlist = $this->_objectManager->get(Wishlist::class);
        $this->customerSession = $this->_objectManager->get(Session::class);
    }

    /**
     * Check Rss response.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Wishlist/_files/two_wishlists_for_two_diff_customers.php
     * @magentoConfigFixture current_store rss/wishlist/active 1
     * @magentoConfigFixture current_store rss/config/active 1
     */
    public function testRssResponse()
    {
        $firstCustomerId = 1;
        $this->customerSession->setCustomerId($firstCustomerId);
        $customer = $this->customerRepository->getById($firstCustomerId);
        $customerEmail = $customer->getEmail();
        $wishlistId = $this->wishlist->loadByCustomerId($firstCustomerId)->getId();
        $this->dispatch($this->getLink($firstCustomerId, $customerEmail, $wishlistId));
        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('<title>John Smith\'s Wishlist</title>', $body);
    }

    /**
     * Check Rss response from `New Products`.
     *
     * @magentoConfigFixture current_store rss/catalog/new 1
     * @magentoConfigFixture current_store rss/config/active 1
     *
     * @return void
     */
    public function testRssResponseNewProducts(): void
    {
        $this->dispatch(self::RSS_NEW_PRODUCTS_PATH);
        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('<title>New Products from Main Website Store</title>', $body);
    }

    /**
     * Check Rss with incorrect wishlist id.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Wishlist/_files/two_wishlists_for_two_diff_customers.php
     * @magentoConfigFixture current_store rss/wishlist/active 1
     * @magentoConfigFixture current_store rss/config/active 1
     */
    public function testRssResponseWithIncorrectWishlistId()
    {
        $firstCustomerId = 1;
        $secondCustomerId = 2;
        $this->customerSession->setCustomerId($firstCustomerId);
        $customer = $this->customerRepository->getById($firstCustomerId);
        $customerEmail = $customer->getEmail();
        $wishlistId = $this->wishlist->loadByCustomerId($secondCustomerId, true)->getId();
        $this->dispatch($this->getLink($firstCustomerId, $customerEmail, $wishlistId));
        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('<title>404 Not Found</title>', $body);
    }

    private function getLink($customerId, $customerEmail, $wishlistId)
    {
        return 'rss/feed/index/type/wishlist/data/'
            . base64_encode($customerId . ',' . $customerEmail)
            . '/wishlist_id/' . $wishlistId;
    }
}
