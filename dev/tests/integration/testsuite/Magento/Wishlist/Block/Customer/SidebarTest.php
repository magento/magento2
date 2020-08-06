<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Block\Customer;

use Magento\Framework\App\ProductMetadata;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Class test sidebar wish list block.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class SidebarTest extends TestCase
{
    private const BLOCK_NAME = 'wishlist_sidebar';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Page */
    private $page;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $productMetadataInterface = $this->objectManager->get(ProductMetadataInterface::class);
        if ($productMetadataInterface->getEdition() !== ProductMetadata::EDITION_NAME) {
            $this->markTestSkipped('Skipped, because this logic is rewritten on EE.');
        }
        $this->page = $this->objectManager->create(Page::class);
    }

    /**
     * @magentoConfigFixture current_store wishlist/general/show_in_sidebar 1
     *
     * @return void
     */
    public function testSidebarWishListVisible(): void
    {
        $this->preparePageLayout();
        $block = $this->page->getLayout()->getBlock(self::BLOCK_NAME);
        $this->assertNotFalse($block);
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                "//div[contains(@class, 'block-wishlist')]//strong[contains(text(), 'My Wish List')]",
                $block->toHtml()
            )
        );
    }

    /**
     * @magentoConfigFixture current_store wishlist/general/show_in_sidebar 0
     *
     * @return void
     */
    public function testSidebarWishListNotVisible(): void
    {
        $this->preparePageLayout();
        $this->assertFalse(
            $this->page->getLayout()->getBlock(self::BLOCK_NAME),
            'Sidebar wish list should not be visible.'
        );
    }

    /**
     * Prepare category page.
     *
     * @return void
     */
    private function preparePageLayout(): void
    {
        $this->page->addHandle([
            'default',
            'catalog_category_view',
        ]);
        $this->page->getLayout()->generateXml();
    }
}
