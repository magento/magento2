<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Block\Customer\Wishlist\Item;

/**
 * Wishlist block customer item column
 *
 * @api
 * @method \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface getItem()
 * @since 2.0.0
 */
class Column extends \Magento\Wishlist\Block\AbstractBlock
{
    /**
     * Checks whether column should be shown in table
     *
     * @return bool
     * @since 2.0.0
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Retrieve block html
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if ($this->isEnabled()) {
            if (!$this->getLayout()) {
                return '';
            }
            foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $child) {
                if ($child) {
                    $child->setItem($this->getItem());
                }
            }
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Retrieve column related javascript code
     *
     * @return string
     * @since 2.0.0
     */
    public function getJs()
    {
        if (!$this->getLayout()) {
            return '';
        }
        $js = '';
        foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $block) {
            $js .= $block->getJs();
        }
        return $js;
    }
}
