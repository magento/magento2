<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Sitemap
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Sitemap\Helper\Data');
    }

    /**
     * @magentoConfigFixture default_store sitemap/limit/max_lines 10
     */
    public function testGetMaximumLinesNumber()
    {
        $this->assertEquals(
            50000,
            $this->_helper->getMaximumLinesNumber(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID)
        );
        $this->assertEquals(
            10,
            $this->_helper->getMaximumLinesNumber(\Magento\Core\Model\AppInterface::DISTRO_STORE_ID)
        );
    }

    /**
     * @magentoConfigFixture default_store sitemap/limit/max_file_size 1024
     */
    public function testGetMaximumFileSize()
    {
        $this->assertEquals(
            10485760, $this->_helper->getMaximumFileSize(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID)
        );
        $this->assertEquals(
            1024,
            $this->_helper->getMaximumFileSize(\Magento\Core\Model\AppInterface::DISTRO_STORE_ID)
        );
    }

    /**
     * @magentoConfigFixture default_store sitemap/category/changefreq montly
     */
    public function testGetCategoryChangefreq()
    {
        $this->assertEquals(
            'daily', $this->_helper->getCategoryChangefreq(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID)
        );
        $this->assertEquals(
            'montly', $this->_helper->getCategoryChangefreq(\Magento\Core\Model\AppInterface::DISTRO_STORE_ID)
        );
    }

    /**
     * @magentoConfigFixture default_store sitemap/product/changefreq montly
     */
    public function testGetProductChangefreq()
    {
        $this->assertEquals(
            'daily', $this->_helper->getProductChangefreq(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID)
        );
        $this->assertEquals(
            'montly', $this->_helper->getProductChangefreq(\Magento\Core\Model\AppInterface::DISTRO_STORE_ID)
        );
    }

    /**
     * @magentoConfigFixture default_store sitemap/page/changefreq montly
     */
    public function testGetPageChangefreq()
    {
        $this->assertEquals(
            'daily',
            $this->_helper->getPageChangefreq(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID)
        );
        $this->assertEquals(
            'montly',
            $this->_helper->getPageChangefreq(\Magento\Core\Model\AppInterface::DISTRO_STORE_ID)
        );
    }

    /**
     * @magentoConfigFixture default_store sitemap/category/priority 100
     */
    public function testGetCategoryPriority()
    {
        $this->assertEquals(0.5, $this->_helper->getCategoryPriority(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID));
        $this->assertEquals(
            100,
            $this->_helper->getCategoryPriority(\Magento\Core\Model\AppInterface::DISTRO_STORE_ID)
        );
    }

    /**
     * @magentoConfigFixture default_store sitemap/product/priority 100
     */
    public function testGetProductPriority()
    {
        $this->assertEquals(1, $this->_helper->getProductPriority(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID));
        $this->assertEquals(100, $this->_helper->getProductPriority(\Magento\Core\Model\AppInterface::DISTRO_STORE_ID));
    }

    /**
     * @magentoConfigFixture default_store sitemap/page/priority 100
     */
    public function testGetPagePriority()
    {
        $this->assertEquals(0.25, $this->_helper->getPagePriority(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID));
        $this->assertEquals(100, $this->_helper->getPagePriority(\Magento\Core\Model\AppInterface::DISTRO_STORE_ID));
    }

    /**
     * @magentoConfigFixture default_store sitemap/search_engines/submission_robots 1
     */
    public function testGetEnableSubmissionRobots()
    {
        $this->assertEquals(
            0,
            $this->_helper->getEnableSubmissionRobots(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID)
        );
        $this->assertEquals(
            1, $this->_helper->getEnableSubmissionRobots(\Magento\Core\Model\AppInterface::DISTRO_STORE_ID)
        );
    }

    /**
     * @magentoConfigFixture default_store sitemap/product/image_include base
     */
    public function testGetProductImageIncludePolicy()
    {
        $this->assertEquals(
            'all', $this->_helper->getProductImageIncludePolicy(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID)
        );
        $this->assertEquals(
            'base', $this->_helper->getProductImageIncludePolicy(\Magento\Core\Model\AppInterface::DISTRO_STORE_ID)
        );
    }
}
