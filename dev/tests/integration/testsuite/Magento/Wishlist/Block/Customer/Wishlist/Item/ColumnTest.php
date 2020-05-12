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
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\Wishlist\Model\GetWishlistByCustomerId;
use Magento\Wishlist\Block\Customer\Wishlist\Items;
use PHPUnit\Framework\TestCase;

/**
 * Test wish list item column.
 *
 * @magentoDbIsolation enabled
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
    private $layout;

    /** @var Column */
    private $block;

    /** @var GetWishlistByCustomerId */
    private $getWishlistItemsByCustomerId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerSession = $this->objectManager->get(Session::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->addBlock(Column::class, 'test');
        $this->layout->addBlock(Text::class, 'child', 'test');
        $this->getWishlistItemsByCustomerId = $this->objectManager->get(GetWishlistByCustomerId::class);
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
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testToHtml(): void
    {
        $item = new \StdClass();
        $this->block->setItem($item);
        $this->block->toHtml();
        $this->assertSame($item, $this->layout->getBlock('child')->getItem());
    }

    /**
     * @return void
     */
    public function testGetJs(): void
    {
        $expected = uniqid();
        $this->layout->getBlock('child')->setJs($expected);
        $this->assertEquals($expected, $this->block->getJs());
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     *
     * @return void
     */
    public function testWishListItemButtons(): void
    {
        $buttons = [
            "Add to Cart button" => "//button[contains(@class, 'tocart')]/span[contains(text(), 'Add to Cart')]",
            "Edit button" => "//a[contains(@class, 'edit')]/span[contains(text(), 'Edit')]",
            "Remove item button" => "//a[contains(@class, 'delete')]/span[contains(text(), 'Remove item')]",
        ];
        $item = $this->getWishlistItemsByCustomerId->getItemBySku(1, 'simple');
        $this->assertNotNull($item);
        $block = $this->getWishListItemsBlock()->getChildBlock('customer.wishlist.item.inner');
        $blockHtml = $block->setItem($item)->toHtml();
        foreach ($buttons as $buttonName => $xpath) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath($xpath, $blockHtml),
                sprintf("%s wasn't found.", $buttonName)
            );
        }
    }

    /**
     * Get wish list items block.
     *
     * @return Items
     */
    private function getWishListItemsBlock(): Items
    {
        $page = $this->objectManager->create(PageFactory::class)->create();
        $page->addHandle([
            'default',
            'wishlist_index_index',
        ]);
        $page->getLayout()->generateXml();

        return $page->getLayout()->getBlock('customer.wishlist.items');
    }
}
