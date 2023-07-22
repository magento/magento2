<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Block\Adminhtml\Widget\Grid\Column\Filter;

use Magento\Backend\Block\Widget\Grid\Column\Filter\Text as BackendText;

class Text extends BackendText
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
