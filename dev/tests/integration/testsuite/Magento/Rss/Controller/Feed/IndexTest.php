<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rss\Controller\Feed;

class IndexTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @var \Magento\Rss\Model\UrlBuilder
     */
    private $urlBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Wishlist\Model\Wishlist
     */
    private $wishlist;

    /**
     * @var
     */
    private $customerSession;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlBuilder = $this->_objectManager->get(\Magento\Rss\Model\UrlBuilder::class);
        $this->customerRepository = $this->_objectManager->get(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->wishlist = $this->_objectManager->get(\Magento\Wishlist\Model\Wishlist::class);
        $this->customerSession = $this->_objectManager->get(\Magento\Customer\Model\Session::class);
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
