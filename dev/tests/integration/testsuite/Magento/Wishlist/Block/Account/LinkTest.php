<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Block\Account;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks My Wish List link displaying in account dashboard
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class LinkTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Page */
    private $page;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->page = $this->objectManager->create(Page::class);
    }

    /**
     * @return void
     */
    public function testNewsletterLink(): void
    {
        $this->preparePage();
        $block = $this->page->getLayout()->getBlock('customer-account-navigation-wish-list-link');
        $this->assertNotFalse($block);
        $html = $block->toHtml();
        $this->assertContains('wishlist/', $html);
        $this->assertEquals('My Wish List', strip_tags($html));
    }

    /**
     * @magentoConfigFixture current_store wishlist/general/active 0
     *
     * @return void
     */
    public function testNewsletterLinkDisabled(): void
    {
        $this->preparePage();
        $block = $this->page->getLayout()->getBlock('customer-account-navigation-wish-list-link');
        $this->assertFalse($block);
    }

    /**
     * Prepare page before render
     *
     * @return void
     */
    private function preparePage(): void
    {
        $this->page->addHandle([
            'default',
            'customer_account',
        ]);
        $this->page->getLayout()->generateXml();
    }
}
