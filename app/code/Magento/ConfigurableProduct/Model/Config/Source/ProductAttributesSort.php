<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Config\Source;

class ProductAttributesSort implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options array
     *
     * @var array
     */
    protected $options;

    const PER_PRODUCT = 0;

    const GLOBAL_ATTRIBUTE_SETTING = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                ['label' => __('Position Per Product'), 'value' => self::PER_PRODUCT],
                ['label' => __('Global Attribute Setting'), 'value' => self::GLOBAL_ATTRIBUTE_SETTING],
            ];
        }
        return $this->options;
    }
}
