<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api;

/**
 * Interface for managing customer attributes metadata.
 * @api
 */
interface CustomerMetadataManagementInterface extends MetadataManagementInterface
{
    const ENTITY_TYPE_CUSTOMER = 'customer';
}
