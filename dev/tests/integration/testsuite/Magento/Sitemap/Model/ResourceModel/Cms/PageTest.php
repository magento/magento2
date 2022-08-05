<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model\ResourceModel\Cms;

use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for Cms Page resource model.
 */
class PageTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var Page
     */
    private $page;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->page = Bootstrap::getObjectManager()->get(Page::class);
    }

    /**
     * Test Page::getCollection() will exclude home and no-route cms pages for site map.
     *
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Cms/_files/pages.php
     * @return void
     */
    public function testGetCollection()
    {
        $excludedUrls = ['no-route', 'home'];
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $result = $this->page->getCollection($storeManager->getDefaultStoreView()->getId());
        $this->assertNotEmpty($result);
        foreach ($result as $item) {
            $this->assertFalse(in_array($item->getUrl(), $excludedUrls));
        }
    }
}
