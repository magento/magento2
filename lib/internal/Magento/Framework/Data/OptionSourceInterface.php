<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

/**
 * Source of option values in a form of value-label pairs
 *
 * @api
 * @since 2.0.0
 */
interface OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     * @since 2.0.0
     */
    public function toOptionArray();
}
