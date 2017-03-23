<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Price display type source model
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Model\System\Config\Source\Tax\Display;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @return array
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
