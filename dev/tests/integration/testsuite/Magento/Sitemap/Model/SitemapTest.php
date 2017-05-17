<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model;

use Magento\Framework\App\CacheInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Filesystem;

/**
 * Tests class for Magento\Sitemap\Model\Sitemap.
 *
 */
class SitemapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Sitemap\Model\Sitemap
     */
    private $sitemap;

    /**
     * Removes cached configuration and reinitializes the application.
     */
    private function refreshConfiguration()
    {
        $this->objectManager->get(CacheInterface::class)
            ->clean([\Magento\Framework\App\Config::CACHE_TAG]);
        Bootstrap::getInstance()->reinitialize();
    }

    /**
     * Set up
     */
    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->sitemap = $this->objectManager->create(Sitemap::class);
    }

    /**
     * Test generating sitemap.xml file when secure url option is on.
     *
     * @magentoDataFixture Magento/Sitemap/_files/sitemap_secure.php
     */
    public function testGenerationWithAdminSecureUrl()
    {
        self::refreshConfiguration();
        $this->sitemap->setData(
            [
                'sitemap_id' => '1',
                'sitemap_filename' => 'sitemap.xml',
                'sitemap_path' => '/',
                'store_id' => '1'
            ]
        );
        $result = $this->sitemap->generateXml();
        $filename = BP . $result->getSitemapPath() . $result->getSitemapFilename();

        $this->assertFileExists($filename, 'File not exists! ' . $filename);
        $file = $this->objectManager->get(\Magento\Framework\Filesystem\Io\File::class);
        $file_content = $file->read($filename);

        $this->assertNotContains('https', $file_content, "File must not contain https in sitemap file! ");
    }
}
