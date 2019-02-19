<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

/**
 * Interface \Magento\Quote\Model\Quote\Address\CustomAttributeListInterface
 *
 */
interface CustomAttributeListInterface
{
    /**
     * Retrieve list of quote address custom attributes
     *
     * @return array
     */
    public function getAttributes();
}
