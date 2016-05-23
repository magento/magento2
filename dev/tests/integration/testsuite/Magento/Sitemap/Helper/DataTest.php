<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sitemap\Helper\Data
     */
    protected $_helper = null;

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Sitemap\Helper\Data'
        );
    }

    /**
     * @magentoConfigFixture default_store sitemap/limit/max_lines 10
     */
    public function testGetMaximumLinesNumber()
    {
        $this->assertEquals(
            50000,
            $this->_helper->getMaximumLinesNumber(\Magento\Store\Model\Store::DEFAULT_STORE_ID)
        );
        $this->assertEquals(10, $this->_helper->getMaximumLinesNumber(\Magento\Store\Model\Store::DISTRO_STORE_ID));
    }

    /**
     * @magentoConfigFixture default_store sitemap/limit/max_file_size 1024
     */
    public function testGetMaximumFileSize()
    {
        $this->assertEquals(
            10485760,
            $this->_helper->getMaximumFileSize(\Magento\Store\Model\Store::DEFAULT_STORE_ID)
        );
        $this->assertEquals(1024, $this->_helper->getMaximumFileSize(\Magento\Store\Model\Store::DISTRO_STORE_ID));
    }

    /**
     * @magentoConfigFixture default_store sitemap/category/changefreq montly
     */
    public function testGetCategoryChangefreq()
    {
        $this->assertEquals(
            'daily',
            $this->_helper->getCategoryChangefreq(\Magento\Store\Model\Store::DEFAULT_STORE_ID)
        );
        $this->assertEquals(
            'montly',
            $this->_helper->getCategoryChangefreq(\Magento\Store\Model\Store::DISTRO_STORE_ID)
        );
    }

    /**
     * @magentoConfigFixture default_store sitemap/product/changefreq montly
     */
    public function testGetProductChangefreq()
    {
        $this->assertEquals(
            'daily',
            $this->_helper->getProductChangefreq(\Magento\Store\Model\Store::DEFAULT_STORE_ID)
        );
        $this->assertEquals(
            'montly',
            $this->_helper->getProductChangefreq(\Magento\Store\Model\Store::DISTRO_STORE_ID)
        );
    }

    /**
     * @magentoConfigFixture default_store sitemap/page/changefreq montly
     */
    public function testGetPageChangefreq()
    {
        $this->assertEquals('daily', $this->_helper->getPageChangefreq(\Magento\Store\Model\Store::DEFAULT_STORE_ID));
        $this->assertEquals('montly', $this->_helper->getPageChangefreq(\Magento\Store\Model\Store::DISTRO_STORE_ID));
    }

    /**
     * @magentoConfigFixture default_store sitemap/category/priority 100
     */
    public function testGetCategoryPriority()
    {
        $this->assertEquals(0.5, $this->_helper->getCategoryPriority(\Magento\Store\Model\Store::DEFAULT_STORE_ID));
        $this->assertEquals(100, $this->_helper->getCategoryPriority(\Magento\Store\Model\Store::DISTRO_STORE_ID));
    }

    /**
     * @magentoConfigFixture default_store sitemap/product/priority 100
     */
    public function testGetProductPriority()
    {
        $this->assertEquals(1, $this->_helper->getProductPriority(\Magento\Store\Model\Store::DEFAULT_STORE_ID));
        $this->assertEquals(100, $this->_helper->getProductPriority(\Magento\Store\Model\Store::DISTRO_STORE_ID));
    }

    /**
     * @magentoConfigFixture default_store sitemap/page/priority 100
     */
    public function testGetPagePriority()
    {
        $this->assertEquals(0.25, $this->_helper->getPagePriority(\Magento\Store\Model\Store::DEFAULT_STORE_ID));
        $this->assertEquals(100, $this->_helper->getPagePriority(\Magento\Store\Model\Store::DISTRO_STORE_ID));
    }

    /**
     * @magentoConfigFixture default_store sitemap/search_engines/submission_robots 1
     */
    public function testGetEnableSubmissionRobots()
    {
        $this->assertEquals(
            0,
            $this->_helper->getEnableSubmissionRobots(\Magento\Store\Model\Store::DEFAULT_STORE_ID)
        );
        $this->assertEquals(1, $this->_helper->getEnableSubmissionRobots(\Magento\Store\Model\Store::DISTRO_STORE_ID));
    }

    /**
     * @magentoConfigFixture default_store sitemap/product/image_include base
     */
    public function testGetProductImageIncludePolicy()
    {
        $this->assertEquals(
            'all',
            $this->_helper->getProductImageIncludePolicy(\Magento\Store\Model\Store::DEFAULT_STORE_ID)
        );
        $this->assertEquals(
            'base',
            $this->_helper->getProductImageIncludePolicy(\Magento\Store\Model\Store::DISTRO_STORE_ID)
        );
    }
}
