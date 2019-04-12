<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rss\Controller\Feed;

/**
 * Test for \Magento\Rss\Controller\Feed\Index
 */
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
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @inheritdoc
     */
    protected function setUp()
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
     * @return void
     */
    public function testRssResponse()
    {
        $customerEmail = 'customer@example.com';
        $customer = $this->customerRepository->get($customerEmail);
        $customerId = $customer->getId();
        $this->customerSession->setCustomerId($customerId);
        $wishlistId = $this->wishlist->loadByCustomerId($customerId)->getId();
        $this->dispatch($this->getLink($customerId, $customerEmail, $wishlistId));
        $body = $this->getResponse()->getBody();

        $this->assertContains('John Smith\'s Wishlist', $body);
    }

    /**
     * Check Rss with incorrect wishlist id.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Wishlist/_files/two_wishlists_for_two_diff_customers.php
     * @magentoConfigFixture current_store rss/wishlist/active 1
     * @magentoConfigFixture current_store rss/config/active 1
     * @return void
     */
    public function testRssResponseWithIncorrectWishlistId()
    {
        $firstCustomerEmail = 'customer@example.com';
        $secondCustomerEmail = 'customer_two@example.com';
        $firstCustomer = $this->customerRepository->get($firstCustomerEmail);
        $secondCustomer = $this->customerRepository->get($secondCustomerEmail);

        $firstCustomerId = $firstCustomer->getId();
        $secondCustomerId = $secondCustomer->getId();
        $this->customerSession->setCustomerId($firstCustomerId);
        $wishlistId = $this->wishlist->loadByCustomerId($secondCustomerId, true)->getId();
        $this->dispatch($this->getLink($firstCustomerId, $firstCustomerEmail, $wishlistId));
        $body = $this->getResponse()->getBody();

        $this->assertContains('<title>404 Not Found</title>', $body);
    }

    /**
     * @param mixed $customerId
     * @param string $customerEmail
     * @param mixed $wishlistId
     * @return string
     */
    private function getLink($customerId, string $customerEmail, $wishlistId): string
    {
        return 'rss/feed/index/type/wishlist/data/'
            . base64_encode($customerId . ',' . $customerEmail)
            . '/wishlist_id/' . $wishlistId;
    }
}
