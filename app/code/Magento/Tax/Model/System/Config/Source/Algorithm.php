<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Config\Source;

/**
 * Class \Magento\Tax\Model\System\Config\Source\Algorithm
 *
 * @since 2.0.0
 */
class Algorithm implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_options;

    /**
     * Initialize the options array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return $this->_options;
    }
}
