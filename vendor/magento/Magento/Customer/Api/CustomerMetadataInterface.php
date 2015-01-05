<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Api;

/**
 * Interface for retrieval information about customer attributes metadata.
 */
interface CustomerMetadataInterface extends MetadataInterface
{
    const ATTRIBUTE_SET_ID_CUSTOMER = 1;

    const ENTITY_TYPE_CUSTOMER = 'customer';

    const DATA_INTERFACE_NAME = 'Magento\Customer\Api\Data\CustomerInterface';
}
