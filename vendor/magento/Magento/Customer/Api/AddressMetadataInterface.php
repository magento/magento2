<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Api;

/**
 * Interface for retrieval information about customer address attributes metadata.
 */
interface AddressMetadataInterface extends MetadataInterface
{
    const ATTRIBUTE_SET_ID_ADDRESS = 2;

    const ENTITY_TYPE_ADDRESS = 'customer_address';

    const DATA_INTERFACE_NAME = 'Magento\Customer\Api\Data\AddressInterface';
}
