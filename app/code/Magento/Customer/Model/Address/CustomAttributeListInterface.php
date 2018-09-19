<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Address;

/**
 * @api
 * @since 100.0.6
 */
interface CustomAttributeListInterface
{
    /**
     * Retrieve list of customer addresses custom attributes
     *
     * @return array
     * @since 100.0.6
     */
    public function getAttributes();
}
