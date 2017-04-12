<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Block\Product;

/**
 * Reports Recently Compared Products Block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Compared extends \Magento\Reports\Block\Product\AbstractProduct
{
    /**
     * Config path for compared products count
     */
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
        return $this->_scopeConfig->getValue(
            self::XML_PATH_RECENTLY_COMPARED_COUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
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
