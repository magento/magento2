<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Product;

/**
 * Reports Recently Viewed Products Block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Viewed extends \Magento\Reports\Block\Product\AbstractProduct implements \Magento\Framework\View\Block\IdentityInterface
{
    const XML_PATH_RECENTLY_VIEWED_COUNT = 'catalog/recently_products/viewed_count';

    /**
     * Viewed Product Index type
     *
     * @var string
     */
    protected $_indexType = \Magento\Reports\Model\Product\Index\Factory::TYPE_VIEWED;

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
        return $this->_scopeConfig->getValue(self::XML_PATH_RECENTLY_VIEWED_COUNT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Added predefined ids support
     *
     * @return int
     */
    public function getCount()
    {
        $ids = $this->getProductIds();
        if (!empty($ids)) {
            return count($ids);
        }
        return parent::getCount();
    }

    /**
     * Prepare to html
     * check has viewed products
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->setRecentlyViewedProducts($this->getItemsCollection());
        return parent::_toHtml();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getItemsCollection() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }
        return $identities;
    }
}
