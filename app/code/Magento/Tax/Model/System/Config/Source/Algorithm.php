<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Tax\Model\Calculation;

class Algorithm implements ArrayInterface
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
            ['value' => Calculation::CALC_UNIT_BASE, 'label' => __('Unit Price')],
            ['value' => Calculation::CALC_ROW_BASE, 'label' => __('Row Total')],
            ['value' => Calculation::CALC_TOTAL_BASE, 'label' => __('Total')],
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
