<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid;

class ManualProduct extends \Magento\Backend\Block\Template
{
    /**
     * @return bool
     */
    public function isHasRows()
    {
        /** @var $grid \Magento\Backend\Block\Widget\Grid */
        $grid = $this->getChildBlock('grid');
        return (bool)$grid->getPreparedCollection()->getSize();
    }
}
