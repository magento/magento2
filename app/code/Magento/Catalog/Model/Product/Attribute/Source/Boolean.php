<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Source;

/**
 * Product attribute source model for enable/disable option
 *
 * @api
 */
class Boolean extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{
    /**
     * Value of 'Use Config' option
     */
    const VALUE_USE_CONFIG = 2;

    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('Yes'), 'value' => static::VALUE_YES],
                ['label' => __('No'), 'value' => static::VALUE_NO],
                ['label' => __('Use config'), 'value' => static::VALUE_USE_CONFIG],
            ];
        }
        return $this->_options;
    }
}
