<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Config\Source;

class Algorithm implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * Initialize the options array
     */
    public function __construct()
    {
        $this->_options = [
            ['value' => \Magento\Tax\Model\Calculation::CALC_UNIT_BASE, 'label' => __('Unit Price')],
            ['value' => \Magento\Tax\Model\Calculation::CALC_ROW_BASE, 'label' => __('Row Total')],
            ['value' => \Magento\Tax\Model\Calculation::CALC_TOTAL_BASE, 'label' => __('Total')],
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_options;
    }
}
