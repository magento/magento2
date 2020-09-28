<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Block;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class test link my wish list in customer menu.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoAppIsolation disabled
 */
class LinkTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Link */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Link::class);
    }

    /**
     * @return void
     */
    public function testWishListLinkVisible(): void
    {
        $this->assertStringContainsString('My Wish List', strip_tags($this->block->toHtml()));
    }

    /**
     * @magentoConfigFixture current_store wishlist/general/active 0
     *
     * @return void
     */
    public function testWishListLinkNotVisible(): void
    {
        $this->assertEmpty($this->block->toHtml());
    }
}
