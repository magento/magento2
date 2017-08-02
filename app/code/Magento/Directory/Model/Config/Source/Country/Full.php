<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Config\Source\Country;

/**
 * Options provider for full countries list
 *
 * @api
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Full extends \Magento\Directory\Model\Config\Source\Country implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @param bool $isMultiselect
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray($isMultiselect = false)
    {
        return parent::toOptionArray(true);
    }
}
