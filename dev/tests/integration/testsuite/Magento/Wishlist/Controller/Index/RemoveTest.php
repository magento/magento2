<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\Wishlist\Model\ResourceModel\Item\Collection as WishlistCollection;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Test for remove product from wish list.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
 */
class RemoveTest extends AbstractController
{
    /** @var Session */
    private $customerSession;

    /** @var WishlistFactory */
    private $wishlistFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->wishlistFactory = $this->_objectManager->get(WishlistFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->customerSession->setCustomerId(null);

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testRemoveProductFromWishList(): void
    {
        $customerId = 1;
        $this->customerSession->setCustomerId($customerId);
        $item = $this->getWishListItemsByCustomerId($customerId)->getFirstItem();
        $productName = $item->getProduct()->getName();
        $this->getRequest()->setParam('item', $item->getId())->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('wishlist/index/remove');
        $message = sprintf("\n%s has been removed from your Wish List.\n", $productName);
        $this->assertSessionMessages($this->equalTo([(string)__($message)]), MessageInterface::TYPE_SUCCESS);
        $this->assertCount(0, $this->getWishListItemsByCustomerId($customerId));
    }

    /**
     * @return void
     */
    public function testRemoveNotExistingItemFromWishList(): void
    {
        $this->customerSession->setCustomerId(1);
        $this->getRequest()->setParams(['item' => 989])->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('wishlist/index/remove');
        $this->assert404NotFound();
    }

    /**
     * Get wish list items collection.
     *
     * @param int $customerId
     * @return WishlistCollection
     */
    private function getWishListItemsByCustomerId(int $customerId): WishlistCollection
    {
        return $this->wishlistFactory->create()->loadByCustomerId($customerId)->getItemCollection();
    }
}
