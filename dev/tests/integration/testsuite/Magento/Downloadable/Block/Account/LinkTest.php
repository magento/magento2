<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Block\Account;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks My Downloadable Product link displaying in account dashboard
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->page = $this->objectManager->get(PageFactory::class)->create();
    }

    /**
     * @return void
     */
    public function testMyDownloadableProductLink(): void
    {
        $this->preparePage();
        $block = $this->page->getLayout()->getBlock('customer-account-navigation-downloadable-products-link');
        $this->assertNotFalse($block);
        $html = $block->toHtml();
        $this->assertStringContainsString('downloadable/customer/products', $html);
        $this->assertEquals('My Downloadable Products', strip_tags($html));
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
