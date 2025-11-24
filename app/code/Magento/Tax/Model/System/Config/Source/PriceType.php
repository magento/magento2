<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Tax\Model\System\Config\Source;

class PriceType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->_options = [
            ['value' => 0, 'label' => __('Excluding Tax')],
            ['value' => 1, 'label' => __('Including Tax')],
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
