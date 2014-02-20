<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Service\V1;

/**
 * Manipulate Customer Metadata Attributes *
 */
interface CustomerMetadataServiceInterface
{
    const CUSTOMER_ATTRIBUTE_SET_ID = 1;
    const ADDRESS_ATTRIBUTE_SET_ID = 2;

    /**
     * Retrieve Attribute Metadata
     *
     * @param   mixed $entityType
     * @param   mixed $attributeCode
     * @return Dto\Eav\AttributeMetadata
     */
    public function getAttributeMetadata($entityType, $attributeCode);

    /**
     * Returns all known attributes metadata for a given entity type
     *
     * @param string $entityType
     * @param int $attributeSetId
     * @param int $storeId
     * @return Dto\Eav\AttributeMetadata[]
     */
    public function getAllAttributeSetMetadata($entityType, $attributeSetId = 0, $storeId = null);

    /**
     * Retrieve all attributes for entityType filtered by form code
     *
     * @param $entityType
     * @param $formCode
     * @return Dto\Eav\AttributeMetadata[]
     */
    public function getAttributes($entityType, $formCode);

    /**
     * Retrieve Customer EAV attribute metadata
     *
     * @param string $attributeCode
     * @return Dto\Eav\AttributeMetadata
     */
    public function getCustomerAttributeMetadata($attributeCode);

    /**
     * Returns all attribute metadata for customers
     *
     * @return Dto\Eav\AttributeMetadata[]
     */
    public function getAllCustomerAttributeMetadata();

    /**
     * Retrieve Customer Addresses EAV attribute metadata
     *
     * @param string $attributeCode
     * @return Dto\Eav\AttributeMetadata
     */
    public function getAddressAttributeMetadata($attributeCode);

    /**
     * Returns all attribute metadata for Addresses
     *
     * @return Dto\Eav\AttributeMetadata[]
     */
    public function getAllAddressAttributeMetadata();

}
