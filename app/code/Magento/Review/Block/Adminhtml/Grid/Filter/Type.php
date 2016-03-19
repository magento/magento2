<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Adminhtml\Grid\Filter;

/**
 * Adminhtml review grid filter by type
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Type extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * Get grid options
     *
     * @return array
     */
    protected function _getOptions()
    {
        return [
            ['label' => '', 'value' => ''],
            ['label' => __('Administrator'), 'value' => 1],
            ['label' => __('Customer'), 'value' => 2],
            ['label' => __('Guest'), 'value' => 3]
        ];
    }

    /**
     * Get condition
     *
     * @return int
     */
    public function getCondition()
    {
        if ($this->getValue() == 1) {
            return 1;
        } elseif ($this->getValue() == 2) {
            return 2;
        } else {
            return 3;
        }
    }
}// Class \Magento\Review\Block\Adminhtml\Grid\Filter\Type END
