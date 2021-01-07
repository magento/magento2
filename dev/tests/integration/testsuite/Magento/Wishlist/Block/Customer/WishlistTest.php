<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Block\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Class test block wish list on customer account page.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation disabled
 * @magentoAppIsolation disabled
 */
class WishlistTest extends TestCase
{
    private const ITEMS_COUNT_XPATH = "//div[contains(@class, 'pager')]//span[contains(@class, 'toolbar-number')"
    . " and contains(text(), '%s Item')]";

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Page */
    private $page;

    /** @var Session */
    private $customerSession;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->page = $this->objectManager->create(Page::class);
        $this->customerSession = $this->objectManager->get(Session::class);
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
     * @magentoConfigFixture current_store wishlist/wishlist_link/use_qty 0
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_product_qty_three.php
     *
     * @return void
     */
    public function testDisplayNumberOfItemsInWishList(): void
    {
        $this->customerSession->setCustomerId(1);
        $pagerBlockHtml = $this->getWishListBlock()->getChildBlock('wishlist_item_pager')->toHtml();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(sprintf(self::ITEMS_COUNT_XPATH, 1), $pagerBlockHtml),
            "Element items count wasn't found."
        );
    }

    /**
     * @magentoConfigFixture current_store wishlist/wishlist_link/use_qty 1
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_product_qty_three.php
     *
     * @return void
     */
    public function testDisplayItemQuantitiesInWishList(): void
    {
        $this->markTestSkipped('Test is blocked by issue MC-31595');
        $this->customerSession->setCustomerId(1);
        $pagerBlockHtml = $this->getWishListBlock()->getChildBlock('wishlist_item_pager')->toHtml();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(sprintf(self::ITEMS_COUNT_XPATH, 3), $pagerBlockHtml),
            "Element items count wasn't found."
        );
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     *
     * @return void
     */
    public function testDisplayActionButtonsInWishList(): void
    {
        $buttonsXpath = [
            "//button[contains(@class, 'update') and @type='submit']/span[contains(text(), 'Update Wish List')]",
            "//button[contains(@class, 'share') and @type='submit']/span[contains(text(), 'Share Wish List')]",
            "//button[contains(@class, 'tocart') and @type='button']/span[contains(text(), 'Add All to Cart')]",
        ];
        $this->customerSession->setCustomerId(1);
        $blockHtml = $this->getWishListBlock()->toHtml();
        foreach ($buttonsXpath as $xpath) {
            $this->assertEquals(1, Xpath::getElementsCountForXpath($xpath, $blockHtml));
        }
    }

    /**
     * Get wish list block.
     *
     * @return Wishlist
     */
    private function getWishListBlock(): Wishlist
    {
        $this->page->addHandle([
            'default',
            'wishlist_index_index',
        ]);
        $this->page->getLayout()->generateXml();

        return $this->page->getLayout()->getBlock('customer.wishlist');
    }
}
