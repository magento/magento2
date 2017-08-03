<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Address;

/**
 * @api
 * @since 2.0.5
 */
interface CustomAttributeListInterface
{
    /**
     * Retrieve list of customer addresses custom attributes
     *
     * @return array
     * @since 2.0.5
     */
    public function getAttributes();
}
