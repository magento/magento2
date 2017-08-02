<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

/**
 * Interface \Magento\Quote\Model\Quote\Address\CustomAttributeListInterface
 *
 * @since 2.0.0
 */
interface CustomAttributeListInterface
{
    /**
     * Retrieve list of quote addresss custom attributes
     *
     * @return array
     * @since 2.0.0
     */
    public function getAttributes();
}
