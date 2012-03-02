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
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * SEO tree Categories Sitemap block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Seo_Sitemap_Tree_Category extends Mage_Catalog_Block_Seo_Sitemap_Category
{
    CONST XML_PATH_LINES_PER_PAGE = 'catalog/sitemap/lines_perpage';

    protected $_storeRootCategoryPath = '';
    protected $_storeRootCategoryLevel = 0;
    protected $_total = 0;
    protected $_from = 0;
    protected $_to = 0;
    protected $_currentPage = 0;
    protected $_categoriesToPages = array();
    /**
     * Initialize categories collection
     *
     * @return Mage_Catalog_Block_Seo_Sitemap_Category
     */
    protected function _prepareLayout()
    {
        $helper = Mage::helper('Mage_Catalog_Helper_Category');
        /* @var $helper Mage_Catalog_Helper_Category */
        $parent = Mage::getModel('Mage_Catalog_Model_Category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load(Mage::app()->getStore()->getRootCategoryId());
        $this->_storeRootCategoryPath = $parent->getPath();
        $this->_storeRootCategoryLevel = $parent->getLevel();
        $this->prepareCategoriesToPages();
        $collection = $this->getTreeCollection();
        $this->setCollection($collection);
        return $this;
    }

    /**
     * Init pager
     *
     * @param string $pagerName
     */
    public function bindPager($pagerName)
    {
        $pager = $this->getLayout()->getBlock($pagerName);
        /* @var $pager Mage_Catalog_Block_Seo_Sitemap_Tree_Pager */
        if ($pager) {
            $pager->setAvailableLimit(array(50 => 50));
            $pager->setTotalNum($this->_total);
            $pager->setLastPageNum(count($this->_categoriesToPages));
            if (!$this->_currentPage) {
                $this->_currentPage = $pager->getCurrentPage();
                $this->_prepareCollection();
            }
            $pager->setFirstNum($this->_from);
            $pager->setLastNum($this->_to);
            $pager->setCollection($this->getCollection());
            $pager->setShowPerPage(false);
        }
    }

    /**
     * Prepare array of categories separated into pages
     *
     * @return Mage_Catalog_Block_Seo_Sitemap_Tree_Category
     */
    public function prepareCategoriesToPages()
    {
        $linesPerPage = Mage::getStoreConfig(self::XML_PATH_LINES_PER_PAGE);
        $tmpCollection = Mage::getModel('Mage_Catalog_Model_Category')->getCollection()
            ->addIsActiveFilter()
            ->addPathsFilter($this->_storeRootCategoryPath . '/')
            ->addLevelFilter($this->_storeRootCategoryLevel + 1)
            ->addOrderField('path');
        $count = 0;
        $page = 1;
        $categories = array();
        foreach ($tmpCollection as $item) {
            $children = $item->getChildrenCount()+1;
            $this->_total += $children;
            if (($children+$count) >= $linesPerPage) {
                $categories[$page][$item->getId()] = array(
                    'path' => $item->getPath(),
                    'children_count' => $this->_total
                );
                $page++;
                $count = 0;
                continue;
            }
            $categories[$page][$item->getId()] = array(
                'path' => $item->getPath(),
                'children_count' => $this->_total
            );
            $count += $children;
        }
        $this->_categoriesToPages = $categories;
        return $this;
    }

    /**
     * Return collection of categories
     *
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getTreeCollection()
    {
        $collection = Mage::getModel('Mage_Catalog_Model_Category')->getCollection()
            ->addNameToResult()
            ->addUrlRewriteToResult()
            ->addIsActiveFilter()
            ->addOrderField('path');
        return $collection;
    }

    /**
     * Prepare collection filtered by paths
     *
     * @return Mage_Catalog_Block_Seo_Sitemap_Tree_Category
     */
    protected function _prepareCollection()
    {
        $_to = 0;
        $pathFilter = array();
        if (isset($this->_categoriesToPages[$this->_currentPage])) {
            foreach ($this->_categoriesToPages[$this->_currentPage] as $_categoryId=>$_categoryInfo) {
                $pathFilter[] = $_categoryInfo['path'];
                $_to = max($_to, $_categoryInfo['children_count']);
            }
        }
        if (empty($pathFilter)) {
            $pathFilter = $this->_storeRootCategoryPath . '/';
        }
        $collection = $this->getCollection();
        $collection->addPathsFilter($pathFilter);
        $this->_to = $_to;
        $this->_from = $_to - $collection->count();
        return $this;
    }

    /**
     * Return level of indent
     *
     * @param Mage_Catalog_Model_Category $item
     * @param integer $delta
     * @return integer
     */
    public function getLevel($item, $delta = 1)
    {
        return (int) ($item->getLevel() - $this->_storeRootCategoryLevel - 1) * $delta;
    }

}
