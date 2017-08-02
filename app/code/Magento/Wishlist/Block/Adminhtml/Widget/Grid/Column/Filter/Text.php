<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Block\Adminhtml\Widget\Grid\Column\Filter;

/**
 * Class \Magento\Wishlist\Block\Adminhtml\Widget\Grid\Column\Filter\Text
 *
 * @since 2.0.0
 */
class Text extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{
    /**
     * Override abstract method
     *
     * @return array
     * @since 2.0.0
     */
    public function getCondition()
    {
        return ['like' => $this->getValue()];
    }
}
