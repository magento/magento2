<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Framework\DataObject;

/**
 * Coupon codes grid "Used" column renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Used extends Text
{
    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $value = (int)$row->getData($this->getColumn()->getIndex());
        return empty($value) ? __('No') : __('Yes');
    }
}
