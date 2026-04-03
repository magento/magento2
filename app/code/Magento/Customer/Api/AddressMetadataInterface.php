<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Api;

/**
 * Interface for retrieval information about customer address attributes metadata.
 * @api
 * @since 100.0.2
 */
interface AddressMetadataInterface extends MetadataInterface
{
    const ATTRIBUTE_SET_ID_ADDRESS = 2;

    const ENTITY_TYPE_ADDRESS = 'customer_address';

    const DATA_INTERFACE_NAME = \Magento\Customer\Api\Data\AddressInterface::class;
}
