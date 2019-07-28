<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

/**
 * Interface for retrieval information about customer attributes metadata.
 * @api
 * @since 100.0.2
 */
interface CustomerMetadataInterface extends MetadataInterface
{
    const ATTRIBUTE_SET_ID_CUSTOMER = 1;

    const ENTITY_TYPE_CUSTOMER = 'customer';

    const DATA_INTERFACE_NAME = \Magento\Customer\Api\Data\CustomerInterface::class;
}
