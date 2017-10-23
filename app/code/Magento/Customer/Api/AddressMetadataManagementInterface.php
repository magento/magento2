<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api;

/**
 * Interface for managing customer address attributes metadata.
 * @api
 * @since 100.0.2
 */
interface AddressMetadataManagementInterface extends MetadataManagementInterface
{
    const ENTITY_TYPE_ADDRESS = 'customer_address';
}
