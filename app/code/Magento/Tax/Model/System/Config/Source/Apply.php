<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Config\Source;

/**
 * Class \Magento\Tax\Model\System\Config\Source\Apply
 *
 * @since 2.0.0
 */
class Apply implements \Magento\Framework\Option\ArrayInterface
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
            ['value' => 0, 'label' => __('Before Discount')],
            ['value' => 1, 'label' => __('After Discount')],
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
