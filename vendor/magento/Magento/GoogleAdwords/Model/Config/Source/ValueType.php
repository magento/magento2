<?php
/**
 * Google AdWords conversation value type source
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GoogleAdwords\Model\Config\Source;

class ValueType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get conversation value type option
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_TYPE_DYNAMIC,
                'label' => __('Dynamic'),
            ],
            [
                'value' => \Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_TYPE_CONSTANT,
                'label' => __('Constant')
            ]
        ];
    }
}
