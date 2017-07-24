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
        $defaultSubmission = $this->model->getEnableSubmissionRobots(Store::DEFAULT_STORE_ID);
        $this->assertEquals(0, $defaultSubmission);
        $distroEnableSubmission = $this->model->getEnableSubmissionRobots(Store::DISTRO_STORE_ID);
        $this->assertEquals(1, $distroEnableSubmission);
    }

    /**
     * @magentoConfigFixture default_store sitemap/limit/max_lines 10
     */
    public function testGetMaximumLinesNumber()
    {
        $defaultLinesNumber = $this->model->getMaximumLinesNumber(Store::DEFAULT_STORE_ID);
        $this->assertEquals(50000, $defaultLinesNumber);
        $distroLinesNumber = $this->model->getMaximumLinesNumber(Store::DISTRO_STORE_ID);
        $this->assertEquals(10, $distroLinesNumber);
    }

    /**
     * @magentoConfigFixture default_store sitemap/limit/max_file_size 1024
     */
    public function testGetMaximumFileSize()
    {
        $defaultFileSize = $this->model->getMaximumFileSize(Store::DEFAULT_STORE_ID);
        $this->assertEquals(10485760, $defaultFileSize);
        $distroFileSize = $this->model->getMaximumFileSize(Store::DISTRO_STORE_ID);
        $this->assertEquals(1024, $distroFileSize);
    }

    /**
     * @magentoConfigFixture default_store sitemap/product/image_include base
     */
    public function testGetProductImageIncludePolicy()
    {
        $defaultPolicy = $this->model->getProductImageIncludePolicy(Store::DEFAULT_STORE_ID);
        $this->assertEquals('all', $defaultPolicy);
        $distroPolicy = $this->model->getProductImageIncludePolicy(Store::DISTRO_STORE_ID);
        $this->assertEquals('base', $distroPolicy);
    }
}
