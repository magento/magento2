<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\Reports\Block\Product;

use \Magento\Framework\DataObject\IdentityInterface;

/**
 * Reports Recently Viewed Products Block
 *
 * @deprecated 100.2.0
 * @see nothing
 */
class Viewed extends AbstractProduct implements IdentityInterface
{
    /**
     * Config path to recently viewed product count
     */
    public const XML_PATH_RECENTLY_VIEWED_COUNT = 'catalog/recently_products/viewed_count';

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
        return $this->_scopeConfig->getValue(
            self::XML_PATH_RECENTLY_VIEWED_COUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
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
     * Prepare to html check has viewed products
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
            $identities[] = $item->getIdentities();
        }
        return array_merge([], ...$identities);
    }
}
