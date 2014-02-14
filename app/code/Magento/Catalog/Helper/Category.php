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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Helper;

use Magento\App\Helper\AbstractHelper;
use Magento\Catalog\Model\Category as ModelCategory;
use Magento\Core\Model\Store;

/**
 * Catalog category helper
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Category extends AbstractHelper
{
    const XML_PATH_CATEGORY_URL_SUFFIX          = 'catalog/seo/category_url_suffix';
    const XML_PATH_USE_CATEGORY_CANONICAL_TAG   = 'catalog/seo/category_canonical_tag';
    const XML_PATH_CATEGORY_ROOT_ID             = 'catalog/category/root_id';

    /**
     * Store categories cache
     *
     * @var array
     */
    protected $_storeCategories = array();

    /**
     * Cache for category rewrite suffix
     *
     * @var array
     */
    protected $_categoryUrlSuffix = array();

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Lib data collection factory
     *
     * @var \Magento\Data\CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Data\CollectionFactory $dataCollectionFactory
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Data\CollectionFactory $dataCollectionFactory
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_storeManager = $storeManager;
        $this->_dataCollectionFactory = $dataCollectionFactory;
        $this->_coreStoreConfig = $coreStoreConfig;
        parent::__construct($context);
    }

    /**
     * Retrieve current store categories
     *
     * @param bool|string $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return \Magento\Data\Tree\Node\Collection|\Magento\Catalog\Model\Resource\Category\Collection|array
     */
    public function getStoreCategories($sorted=false, $asCollection=false, $toLoad=true)
    {
        $parent     = $this->_storeManager->getStore()->getRootCategoryId();
        $cacheKey   = sprintf('%d-%d-%d-%d', $parent, $sorted, $asCollection, $toLoad);
        if (isset($this->_storeCategories[$cacheKey])) {
            return $this->_storeCategories[$cacheKey];
        }

        /**
         * Check if parent node of the store still exists
         */
        $category = $this->_categoryFactory->create();
        /* @var $category ModelCategory */
        if (!$category->checkId($parent)) {
            if ($asCollection) {
                return $this->_dataCollectionFactory->create();
            }
            return array();
        }

        $recursionLevel  = max(0, (int) $this->_storeManager->getStore()->getConfig('catalog/navigation/max_depth'));
        $storeCategories = $category->getCategories($parent, $recursionLevel, $sorted, $asCollection, $toLoad);

        $this->_storeCategories[$cacheKey] = $storeCategories;
        return $storeCategories;
    }

    /**
     * Retrieve category url
     *
     * @param ModelCategory $category
     * @return string
     */
    public function getCategoryUrl($category)
    {
        if ($category instanceof ModelCategory) {
            return $category->getUrl();
        }
        return $this->_categoryFactory->create()
            ->setData($category->getData())
            ->getUrl();
    }

    /**
     * Check if a category can be shown
     *
     * @param ModelCategory|int $category
     * @return bool
     */
    public function canShow($category)
    {
        if (is_int($category)) {
            $category = $this->_categoryFactory->create()->load($category);
        }

        if (!$category->getId()) {
            return false;
        }

        if (!$category->getIsActive()) {
            return false;
        }
        if (!$category->isInRootCategoryList()) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve category rewrite suffix for store
     *
     * @param int $storeId
     * @return string
     */
    public function getCategoryUrlSuffix($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->_storeManager->getStore()->getId();
        }

        if (!isset($this->_categoryUrlSuffix[$storeId])) {
            $this->_categoryUrlSuffix[$storeId] = $this->_coreStoreConfig->getConfig(
                self::XML_PATH_CATEGORY_URL_SUFFIX, $storeId
            );
        }
        return $this->_categoryUrlSuffix[$storeId];
    }

    /**
     * Retrieve clear url for category as parent
     *
     * @param string $urlPath
     * @param bool $slash
     * @param int $storeId
     * @return string
     */
    public function getCategoryUrlPath($urlPath, $slash = false, $storeId = null)
    {
        if (!$this->getCategoryUrlSuffix($storeId)) {
            return $urlPath;
        }

        if ($slash) {
            $regexp     = '#('.preg_quote($this->getCategoryUrlSuffix($storeId), '#').')/$#i';
            $replace    = '/';
        }
        else {
            $regexp     = '#('.preg_quote($this->getCategoryUrlSuffix($storeId), '#').')$#i';
            $replace    = '';
        }

        return preg_replace($regexp, $replace, $urlPath);
    }

    /**
     * Check if <link rel="canonical"> can be used for category
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function canUseCanonicalTag($store = null)
    {
        return $this->_coreStoreConfig->getConfig(self::XML_PATH_USE_CATEGORY_CANONICAL_TAG, $store);
    }
}
