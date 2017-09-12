<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Address;

use Magento\Framework\DataObject;

/**
 * Interface RendererInterface
 * @package Magento\Store\Model\Address
 */
interface RendererInterface
{
    /**
     * Format the store address in a specific way
     *
     * @param DataObject $storeInfo
     * @param string $type
     * @return string
     */
    public function format(DataObject $storeInfo, $type = 'html');
}
