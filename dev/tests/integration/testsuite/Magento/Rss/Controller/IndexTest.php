<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rss\Controller;

use Magento\TestFramework\TestCase\AbstractController;

/**
 * Class IndexTest
 */
class IndexTest extends AbstractController
{
    /**
     * Test the RSS page when RSS is enabled and if the RSS link is present in footer
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store rss/config/active 1
     */
    public function testRssPageWhenEnabled()
    {
        $this->dispatch('rss/index/index');
        $body = $this->getResponse()->getBody();
        $this->assertContains('RSS Feeds', $body);
        $this->assertContains('<strong>RSS</strong>', $body);
    }

    /**
     * Test opening the RSS page when RSS is disabled
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store rss/config/active 0
     */
    public function testOpenRssPageWhenDisabled()
    {
        $this->dispatch('rss/index/index');
        $body = $this->getResponse()->getBody();
        $this->assertNotContains('RSS Feeds', $body);
        $this->assertNotContains('<strong>RSS</strong>', $body);
    }

    /**
     * Test new products rss link
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store rss/config/active 1
     * @magentoConfigFixture default_store rss/catalog/new 1
     * @magentoDataFixture Magento/Catalog/controllers/_files/products.php
     */
    public function testNewProductsRssLink()
    {
        $this->dispatch('rss/index/index');
        $this->assertContains('New Products', $this->getResponse()->getBody());
    }

    /**
     * Test categories rss link
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store rss/config/active 1
     * @magentoConfigFixture default_store rss/catalog/category 1
     * @magentoDataFixture Magento/Catalog/_files/category.php
     */
    public function testCategoriesRssLink()
    {
        $this->dispatch('rss/index/index');
        $this->assertContains('Categories', $this->getResponse()->getBody());
    }
}
