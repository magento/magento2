<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rss\Test\Integration\Controller\Feed;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Rss\Model\UrlBuilder;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Wishlist\Model\Wishlist;

class IndexTest extends AbstractBackendController
{
    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Wishlist
     */
    private $wishlist;

    /**
     * @var
     */
    private $customerSession;

    /**
     * @return void
     * @throws AuthenticationException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->urlBuilder = $this->_objectManager->get(UrlBuilder::class);
        $this->customerRepository = $this->_objectManager->get(
            CustomerRepositoryInterface::class
        );
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
    public function testRssResponse(): void
    {
        $firstCustomerId = 1;
        $this->customerSession->setCustomerId($firstCustomerId);
        $customer = $this->customerRepository->getById($firstCustomerId);
        $customerEmail = $customer->getEmail();
        $wishlistId = (int) $this->wishlist->loadByCustomerId($firstCustomerId)->getId();
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
    public function testRssResponseWithIncorrectWishlistId(): void
    {
        $firstCustomerId = 1;
        $secondCustomerId = 2;
        $this->customerSession->setCustomerId($firstCustomerId);
        $customer = $this->customerRepository->getById($firstCustomerId);
        $customerEmail = $customer->getEmail();
        $wishlistId = (int) $this->wishlist->loadByCustomerId($secondCustomerId, true)->getId();
        $this->dispatch($this->getLink($firstCustomerId, $customerEmail, $wishlistId));
        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('<title>404 Not Found</title>', $body);
    }

    /**
     * @param int $customerId
     * @param string $customerEmail
     * @param int $wishlistId
     *
     * @return string
     */
    private function getLink(int $customerId, string $customerEmail, int $wishlistId): string
    {
        return 'rss/feed/index/type/wishlist/data/'
            . base64_encode($customerId . ',' . $customerEmail)
            . '/wishlist_id/' . $wishlistId;
    }
}
