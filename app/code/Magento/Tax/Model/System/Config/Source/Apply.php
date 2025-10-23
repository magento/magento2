<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Tax\Model\System\Config\Source;

class Apply implements \Magento\Framework\Option\ArrayInterface
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
            ['value' => 0, 'label' => __('Before Discount')],
            ['value' => 1, 'label' => __('After Discount')],
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
