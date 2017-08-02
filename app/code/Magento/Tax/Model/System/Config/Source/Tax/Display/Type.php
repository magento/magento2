<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Price display type source model
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Model\System\Config\Source\Tax\Display;

/**
 * Class \Magento\Tax\Model\System\Config\Source\Tax\Display\Type
 *
 * @since 2.0.0
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_options;

    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = [];
            $this->_options[] = [
                'value' => \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX,
                'label' => __('Excluding Tax'),
            ];
            $this->_options[] = [
                'value' => \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX,
                'label' => __('Including Tax'),
            ];
            $this->_options[] = [
                'value' => \Magento\Tax\Model\Config::DISPLAY_TYPE_BOTH,
                'label' => __('Including and Excluding Tax'),
            ];
        }
        return $this->_options;
    }
}
