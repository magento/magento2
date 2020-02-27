<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Block\Customer\Wishlist\Item;

use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Page;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\Wishlist\Block\Customer\Wishlist\Items;
use Magento\Wishlist\Model\WishlistFactory;
use PHPUnit\Framework\TestCase;

/**
 * Test wish list item column.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation disabled
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ColumnTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Session */
    private $customerSession;

    /** @var LayoutInterface */
    private $_layout;

    /** @var Column */
    private $_block;

    /** @var WishlistFactory */
    private $wishlistFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerSession = $this->objectManager->get(Session::class);
        $this->_layout = $this->objectManager->get(LayoutInterface::class);
        $this->_block = $this->_layout->addBlock(Column::class, 'test');
        $this->_layout->addBlock(Text::class, 'child', 'test');
        $this->wishlistFactory = $this->objectManager->get(WishlistFactory::class);
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
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testToHtml(): void
    {
        $item = new \StdClass();
        $this->_block->setItem($item);
        $this->_block->toHtml();
        $this->assertSame($item, $this->_layout->getBlock('child')->getItem());
    }

    /**
     * @return void
     */
    public function testGetJs(): void
    {
        $expected = uniqid();
        $this->_layout->getBlock('child')->setJs($expected);
        $this->assertEquals($expected, $this->_block->getJs());
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     *
     * @return void
     */
    public function testWishListItemButtons(): void
    {
        $buttons = [
            "//button[contains(@class, 'tocart')]/span[contains(text(), 'Add to Cart')]",
            "//a[contains(@class, 'edit')]/span[contains(text(), 'Edit')]",
            "//a[contains(@class, 'delete')]/span[contains(text(), 'Remove item')]",
        ];
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId(1);
        $item = $wishlist->getItemCollection()->getFirstItem();
        $block = $this->getWishListItemsBlockHtml()->getChildBlock('customer.wishlist.item.inner');
        $blockHtml = $block->setItem($item)->toHtml();
        foreach ($buttons as $xpath) {
            $this->assertEquals(1, Xpath::getElementsCountForXpath($xpath, $blockHtml));
        }
    }

    /**
     * Get wish list items block html.
     *
     * @return Items
     */
    private function getWishListItemsBlockHtml(): Items
    {
        $page = $this->objectManager->create(Page::class);
        $page->addHandle([
            'default',
            'wishlist_index_index',
        ]);
        $page->getLayout()->generateXml();

        return $page->getLayout()->getBlock('customer.wishlist.items');
    }
}
