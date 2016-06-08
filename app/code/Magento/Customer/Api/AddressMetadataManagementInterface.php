<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api;

/**
 * Interface for managing customer address attributes metadata.
 * @api
 */
interface AddressMetadataManagementInterface extends MetadataManagementInterface
{
    const ENTITY_TYPE_ADDRESS = 'customer_address';
}
