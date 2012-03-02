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
 * Html page block
 *
 * @category   Mage
 * @package    Mage_Page
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @todo        separate order, mode and pager
 */
class Mage_Catalog_Block_Seo_Sitemap_Tree_Pager extends Mage_Page_Block_Html_Pager
{
    protected $_showPerPage     = false;
    protected $lastPageNumber   = 1;
    protected $_totalNum        = 0;
    protected $_firstNum        = 0;
    protected $_lastNum         = 1;

    public function getCurrentPage($displacement = 0)
    {
        if ($page = (int) $this->getRequest()->getParam($this->getPageVarName()) + $displacement) {
            if ($page > $this->getLastPageNum()) {
                return $this->getLastPageNum();
            }
            return $page;
        }
        return 1;
    }

    public function getLimit()
    {
        $limits = $this->getAvailableLimit();
//        if ($limit = $this->getRequest()->getParam($this->getLimitVarName())) {
//            if (isset($limits[$limit])) {
//                return $limit;
//            }
//        }
        $limits = array_keys($limits);
        return $limits[0];
    }

    public function setCollection($collection)
    {
        $this->_collection = $collection;
//            ->setCurPage($this->getCurrentPage());
        // If not int - then not limit
//        if ((int) $this->getLimit()) {
//            $this->_collection->setPageSize($this->getLimit());
//        }

        return $this;
    }

    /**
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    public function getFirstNum()
    {
        return $this->_firstNum + 1;
    }

    public function setFirstNum($firstNum)
    {
        $this->_firstNum = $firstNum;
        return $this;
    }

    public function getLastNum()
    {
        return $this->_lastNum;
    }

    public function setLastNum($lastNum)
    {
        $this->_lastNum = $lastNum;
        return $this;
    }

    public function getTotalNum()
    {
        return $this->_totalNum;
    }

    public function setTotalNum($totalNum)
    {
        $this->_totalNum = $totalNum;
        return $this;
    }

    public function isFirstPage()
    {
        return $this->getCurrentPage() == 1;
    }

    public function getLastPageNum()
    {
        return $this->_lastPageNumber;
    }

    public function setLastPageNum($lastPageNum)
    {
        $this->_lastPageNumber = $lastPageNum;
        return $this;
    }

    public function isLastPage()
    {
        return $this->getCurrentPage() >= $this->getLastPageNum();
    }

    public function getPages()
    {
        $pages = array();
        if ($this->getLastPageNum() <= $this->_displayPages) {
            $pages = range(1, $this->getLastPageNum());
        } else {
            $half = ceil($this->_displayPages / 2);
            if ($this->getCurrentPage() >= $half && $this->getCurrentPage() <= $this->getLastPageNum() - $half) {
                $start  = ($this->getCurrentPage() - $half) + 1;
                $finish = ($start + $this->_displayPages) - 1;
            } elseif ($this->getCurrentPage() < $half) {
                $start  = 1;
                $finish = $this->_displayPages;
            } elseif ($this->getCurrentPage() > ($this->getLastPageNum() - $half)) {
                $finish = $this->getLastPageNum();
                $start  = $finish - $this->_displayPages + 1;
            }
            $pages = range($start, $finish);
        }

        return $pages;
    }

    public function getPreviousPageUrl()
    {
        return $this->getPageUrl($this->getCurrentPage(-1));
    }

    public function getNextPageUrl()
    {
        return $this->getPageUrl($this->getCurrentPage(+1));
    }

    public function getLastPageUrl()
    {
        return $this->getPageUrl($this->getLastPageNum());
    }

}

