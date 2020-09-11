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
 * Test for update wish list item.
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
 */
class UpdateTest extends AbstractController
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
    public function testUpdateWishListItem(): void
    {
        $this->customerSession->setCustomerId(1);
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'simple');
        $this->assertNotNull($item);
        $params = ['description' => [$item->getId() => 'Some description.'], 'qty' => [$item->getId() => 5]];
        $this->performUpdateWishListItemRequest($params);
        $message = sprintf("%s has been updated in your Wish List.", $item->getProduct()->getName());
        $this->assertSessionMessages($this->equalTo([(string)__($message)]), MessageInterface::TYPE_SUCCESS);
        $this->assertRedirect($this->stringContains('wishlist/index/index/wishlist_id/' . $item->getWishlistId()));
        $updatedItem = $this->getWishlistByCustomerId->getItemBySku(1, 'simple');
        $this->assertNotNull($updatedItem);
        $this->assertEquals(5, $updatedItem->getQty());
        $this->assertEquals('Some description.', $updatedItem->getDescription());
    }

    /**
     * @return void
     */
    public function testUpdateWishListItemZeroQty(): void
    {
        $this->customerSession->setCustomerId(1);
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'simple');
        $this->assertNotNull($item);
        $params = ['description' => [$item->getId() => ''], 'qty' => [$item->getId() => 0]];
        $this->performUpdateWishListItemRequest($params);
        $message = sprintf("%s has been updated in your Wish List.", $item->getProduct()->getName());
        $this->assertSessionMessages($this->equalTo([(string)__($message)]), MessageInterface::TYPE_SUCCESS);
        $this->assertRedirect($this->stringContains('wishlist/index/index/wishlist_id/' . $item->getWishlistId()));
        $this->assertCount(0, $this->getWishlistByCustomerId->execute(1)->getItemCollection());
    }

    /**
     * Perform update wish list item request.
     *
     * @param array $params
     * @return void
     */
    private function performUpdateWishListItemRequest(array $params): void
    {
        $this->getRequest()->setPostValue($params)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('wishlist/index/update');
    }
}
