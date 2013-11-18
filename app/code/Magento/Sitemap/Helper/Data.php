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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sitemap data helper
 *
 * @category   Magento
 * @package    Magento_Sitemap
 */
namespace Magento\Sitemap\Helper;

class Data extends \Magento\Core\Helper\AbstractHelper
{
    /**#@+
     * Limits xpath config settings
     */
    const XML_PATH_MAX_LINES     = 'sitemap/limit/max_lines';
    const XML_PATH_MAX_FILE_SIZE = 'sitemap/limit/max_file_size';
    /**#@-*/

    /**#@+
     * Change frequency xpath config settings
     */
    const XML_PATH_CATEGORY_CHANGEFREQ = 'sitemap/category/changefreq';
    const XML_PATH_PRODUCT_CHANGEFREQ = 'sitemap/product/changefreq';
    const XML_PATH_PAGE_CHANGEFREQ = 'sitemap/page/changefreq';
    /**#@-*/

    /**#@+
     * Change frequency xpath config settings
     */
    const XML_PATH_CATEGORY_PRIORITY = 'sitemap/category/priority';
    const XML_PATH_PRODUCT_PRIORITY = 'sitemap/product/priority';
    const XML_PATH_PAGE_PRIORITY = 'sitemap/page/priority';
    /**#@-*/

    /**#@+
     * Search Engine Submission Settings
     */
    const XML_PATH_SUBMISSION_ROBOTS = 'sitemap/search_engines/submission_robots';
    /**#@-*/

    const XML_PATH_PRODUCT_IMAGES_INCLUDE = 'sitemap/product/image_include';

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     */
    public function __construct(
        \Magento\Core\Helper\Context $context,
        \Magento\Core\Model\Store\Config $coreStoreConfig
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        parent::__construct($context);
    }

    /**
     * Get maximum sitemap.xml URLs number
     *
     * @param int $storeId
     * @return int
     */
    public function getMaximumLinesNumber($storeId)
    {
        return $this->_coreStoreConfig->getConfig(self::XML_PATH_MAX_LINES, $storeId);
    }

    /**
     * Get maximum sitemap.xml file size in bytes
     *
     * @param int $storeId
     * @return int
     */
    public function getMaximumFileSize($storeId)
    {
        return $this->_coreStoreConfig->getConfig(self::XML_PATH_MAX_FILE_SIZE, $storeId);
    }

    /**
     * Get category change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getCategoryChangefreq($storeId)
    {
        return (string) $this->_coreStoreConfig->getConfig(self::XML_PATH_CATEGORY_CHANGEFREQ, $storeId);
    }

    /**
     * Get product change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getProductChangefreq($storeId)
    {
        return (string) $this->_coreStoreConfig->getConfig(self::XML_PATH_PRODUCT_CHANGEFREQ, $storeId);
    }

    /**
     * Get page change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getPageChangefreq($storeId)
    {
        return (string) $this->_coreStoreConfig->getConfig(self::XML_PATH_PAGE_CHANGEFREQ, $storeId);
    }

    /**
     * Get category priority
     *
     * @param int $storeId
     * @return string
     */
    public function getCategoryPriority($storeId)
    {
        return (string) $this->_coreStoreConfig->getConfig(self::XML_PATH_CATEGORY_PRIORITY, $storeId);
    }

    /**
     * Get product priority
     *
     * @param int $storeId
     * @return string
     */
    public function getProductPriority($storeId)
    {
        return (string) $this->_coreStoreConfig->getConfig(self::XML_PATH_PRODUCT_PRIORITY, $storeId);
    }

    /**
     * Get page priority
     *
     * @param int $storeId
     * @return string
     */
    public function getPagePriority($storeId)
    {
        return (string) $this->_coreStoreConfig->getConfig(self::XML_PATH_PAGE_PRIORITY, $storeId);
    }

    /**
     * Get enable Submission to Robots.txt
     *
     * @param int $storeId
     * @return int
     */
    public function getEnableSubmissionRobots($storeId)
    {
        return $this->_coreStoreConfig->getConfig(self::XML_PATH_SUBMISSION_ROBOTS, $storeId);
    }

    /**
     * Get product image include policy
     *
     * @param int $storeId
     * @return string
     */
    public function getProductImageIncludePolicy($storeId)
    {
        return (string) $this->_coreStoreConfig->getConfig(self::XML_PATH_PRODUCT_IMAGES_INCLUDE, $storeId);
    }
}
