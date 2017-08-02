<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Config\Source;

/**
 * Class \Magento\Tax\Model\System\Config\Source\PriceType
 *
 * @since 2.0.0
 */
class PriceType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_options;

    /**
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return $this->_options;
    }
}
