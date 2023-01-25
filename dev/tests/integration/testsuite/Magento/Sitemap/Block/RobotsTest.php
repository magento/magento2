<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Block;

use Magento\Framework\View\LayoutInterface;
use Magento\Sitemap\Model\SitemapFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Sitemap\Block\Robots.
 */
class RobotsTest extends TestCase
{
    private const STUB_SITEMAP_FILENAME = 'sitemap_file.xml';

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var SitemapFactory
     */
    private $sitemapFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        $this->layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        $this->sitemapFactory = Bootstrap::getObjectManager()->get(SitemapFactory::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
    }

    /**
     * Test toHtml with few websites
     *
     * @magentoDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @magentoConfigFixture default_store sitemap/search_engines/submission_robots 1
     * @magentoConfigFixture second_store_view_store sitemap/search_engines/submission_robots 1
     *
     * @return void
     */
    public function testToHtml(): void
    {
        $secondSitemapFile = 'second_' . self::STUB_SITEMAP_FILENAME;

        $this->createSitemap(self::STUB_SITEMAP_FILENAME, 1);
        $this->createSitemap($secondSitemapFile, 2);

        $this->assertStringContainsString(self::STUB_SITEMAP_FILENAME, $this->getToHtmlOutput(1));
        $this->assertStringContainsString($secondSitemapFile, $this->getToHtmlOutput(2));
    }

    /**
     * Returns toHtml output per store
     *
     * @param int $storeId
     * @return string
     */
    private function getToHtmlOutput(int $storeId): string
    {
        $this->storeManager->setCurrentStore($storeId);
        $block = $this->layout->createBlock(Robots::class);

        return $block->toHtml();
    }

    /**
     * Create Sitemap
     *
     * @param string $fileName
     * @param int $storeId
     * @param string $siteMath
     * @return void
     */
    private function createSitemap(string $fileName, int $storeId, string $siteMath = '/'): void
    {
        $model = $this->sitemapFactory->create();
        $model->setData(['sitemap_filename' => $fileName, 'store_id' => $storeId, 'sitemap_path' => $siteMath]);
        $model->save();
    }
}
