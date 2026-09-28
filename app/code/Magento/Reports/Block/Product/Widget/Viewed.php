<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Block\Product\Widget;

/**
 * Reports Recently Viewed Products Widget
 * @deprecated 100.2.0 Since new frontend base widgets are provided
 * @see \Magento\Catalog\Block\Widget\RecentlyViewed
 */
class Viewed extends \Magento\Reports\Block\Product\Viewed implements \Magento\Widget\Block\BlockInterface
{
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addColumnCountLayoutDepend('1column', 5)
            ->addColumnCountLayoutDepend('2columns-left', 4)
            ->addColumnCountLayoutDepend('2columns-right', 4)
            ->addColumnCountLayoutDepend('3columns', 3);
    }
}
