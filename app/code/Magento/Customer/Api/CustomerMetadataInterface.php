<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

/**
 * Interface for retrieval information about customer attributes metadata.
 * @api
 */
interface CustomerMetadataInterface extends MetadataInterface
{
    const ATTRIBUTE_SET_ID_CUSTOMER = 1;

    const ENTITY_TYPE_CUSTOMER = 'customer';

    const DATA_INTERFACE_NAME = 'Magento\Customer\Api\Data\CustomerInterface';
}
