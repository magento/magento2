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
use Magento\TestFramework\Wishlist\Model\GetWishlistByCustomerId;

/**
 * Test for remove product from wish list.
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
 */
class RemoveTest extends AbstractController
{
    /** @var Session */
    private $customerSession;

    /** @var GetWishlistByCustomerId */
    private $getWishlistByCustomerId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->getWishlistByCustomerId = $this->_objectManager->get(GetWishlistByCustomerId::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
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
        $item = $this->getWishlistByCustomerId->getItemBySku($customerId, 'simple');
        $this->assertNotNull($item);
        $productName = $item->getProduct()->getName();
        $this->getRequest()->setParam('item', $item->getId())->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('wishlist/index/remove');
        $message = sprintf("\n%s has been removed from your Wish List.\n", $productName);
        $this->assertSessionMessages($this->equalTo([(string)__($message)]), MessageInterface::TYPE_SUCCESS);
        $this->assertCount(0, $this->getWishlistByCustomerId->execute($customerId)->getItemCollection());
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
}
