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
 * Class test my wish list on customer account page.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
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
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->page = $this->objectManager->create(Page::class);
        $this->customerSession = $this->objectManager->get(Session::class);
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
     * @magentoConfigFixture current_store wishlist/wishlist_link/use_qty 0
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_product_qty_three.php
     *
     * @return void
     */
    public function testDisplayNumberOfItemsInWishList(): void
    {
        $this->customerSession->setCustomerId(1);
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(sprintf(self::ITEMS_COUNT_XPATH, 1), $this->getWishListPagerBlockHtml())
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
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(sprintf(self::ITEMS_COUNT_XPATH, 3), $this->getWishListPagerBlockHtml())
        );
    }

    /**
     * Get wish list pager block html.
     *
     * @return string
     */
    private function getWishListPagerBlockHtml(): string
    {
        $this->page->addHandle([
            'default',
            'wishlist_index_index',
        ]);
        $this->page->getLayout()->generateXml();
        /** @var Wishlist $customerWishlistBlock */
        $customerWishlistBlock = $this->page->getLayout()->getBlock('customer.wishlist');

        return $customerWishlistBlock->getChildBlock('wishlist_item_pager')->toHtml();
    }
}
