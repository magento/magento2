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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Reports\Block\Product;

/**
 * Reports Recently Compared Products Block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Compared extends \Magento\Reports\Block\Product\AbstractProduct
{
    const XML_PATH_RECENTLY_COMPARED_COUNT = 'catalog/recently_products/compared_count';

    /**
     * Compared Product Index type
     *
     * @var string
     */
    protected $_indexType = \Magento\Reports\Model\Product\Index\Factory::TYPE_COMPARED;

    /**
     * Retrieve page size (count)
     *
     * @return int
     */
    public function getPageSize()
    {
        if ($this->hasData('page_size')) {
            return $this->getData('page_size');
        }
        return $this->_scopeConfig->getValue(self::XML_PATH_RECENTLY_COMPARED_COUNT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Prepare to html
     * Check has compared products
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getCount()) {
            return '';
        }

        $this->setRecentlyComparedProducts($this->getItemsCollection());

        return parent::_toHtml();
    }
}
