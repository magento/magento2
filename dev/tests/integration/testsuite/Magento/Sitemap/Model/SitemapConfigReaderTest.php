<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model;

use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

class SitemapConfigReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SitemapConfigReader
     */
    private $model = null;

    protected function setUp()
    {
        $this->model = Bootstrap::getObjectManager()->get(SitemapConfigReader::class);
    }

    /**
     * @magentoConfigFixture default_store sitemap/search_engines/submission_robots 1
     */
    public function testGetEnableSubmissionRobots()
    {
        $this->assertEquals(0, $this->model->getEnableSubmissionRobots(Store::DEFAULT_STORE_ID));
        $this->assertEquals(1, $this->model->getEnableSubmissionRobots(Store::DISTRO_STORE_ID));
    }

    /**
     * @magentoConfigFixture default_store sitemap/limit/max_lines 10
     */
    public function testGetMaximumLinesNumber()
    {
        $this->assertEquals(50000, $this->model->getMaximumLinesNumber(Store::DEFAULT_STORE_ID));
        $this->assertEquals(10, $this->model->getMaximumLinesNumber(Store::DISTRO_STORE_ID));
    }

    /**
     * @magentoConfigFixture default_store sitemap/limit/max_file_size 1024
     */
    public function testGetMaximumFileSize()
    {
        $this->assertEquals(10485760, $this->model->getMaximumFileSize(Store::DEFAULT_STORE_ID));
        $this->assertEquals(1024, $this->model->getMaximumFileSize(Store::DISTRO_STORE_ID));
    }

    /**
     * @magentoConfigFixture default_store sitemap/product/image_include base
     */
    public function testGetProductImageIncludePolicy()
    {
        $this->assertEquals('all', $this->model->getProductImageIncludePolicy(Store::DEFAULT_STORE_ID));
        $this->assertEquals('base', $this->model->getProductImageIncludePolicy(Store::DISTRO_STORE_ID));
    }
}
