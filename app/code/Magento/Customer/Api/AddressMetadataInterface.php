<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

/**
 * Interface for retrieval information about customer address attributes metadata.
 * @api
 * @since 2.0.0
 */
interface AddressMetadataInterface extends MetadataInterface
{
    const ATTRIBUTE_SET_ID_ADDRESS = 2;

    const ENTITY_TYPE_ADDRESS = 'customer_address';

    const DATA_INTERFACE_NAME = \Magento\Customer\Api\Data\AddressInterface::class;
}
