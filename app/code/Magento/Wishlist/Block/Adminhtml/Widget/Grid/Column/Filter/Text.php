<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Block\Adminhtml\Widget\Grid\Column\Filter;

class Text extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{
    /**
     * Override abstract method
     *
     * @return array
     */
    public function getCondition()
    {
        return ['like' => $this->getValue()];
    }
}
