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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Site Map category block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Catalog_Block_Seo_Sitemap_Abstract extends Mage_Core_Block_Template
{

    /**
     * Init pager
     *
     * @param string $pagerName
     */
    public function bindPager($pagerName)
    {
        $pager = $this->getLayout()->getBlock($pagerName);
        /* @var $pager Mage_Page_Html_Pager */
        if ($pager) {
            $pager->setAvailableLimit(array(50 => 50));
            $pager->setCollection($this->getCollection());
            $pager->setShowPerPage(false);
        }
    }

    /**
     * Get item URL
     *
     * In most cases should be overriden in descendant blocks
     *
     * @param Varien_Object $item
     * @return string
     */
    public function getItemUrl($item)
    {
        return $item->getUrl();
    }

}
