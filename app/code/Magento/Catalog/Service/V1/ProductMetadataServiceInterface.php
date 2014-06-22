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
namespace Magento\Catalog\Service\V1;

/**
 * Class ProductMetadataServiceInterface
 * @package Magento\Catalog\Service\V1
 */
interface ProductMetadataServiceInterface
{
    /**#@+
     * Predefined constants
     */
    const ENTITY_TYPE_PRODUCT           = 'catalog_product';

    const ATTRIBUTE_SET_ID_PRODUCT      = 4;
    /**#@-*/

    /**
     * Retrieve custom EAV attribute metadata of product
     *
     * @return array<Data\Eav\AttributeMetadata>
     */
    public function getCustomAttributesMetadata();

    /**
     * Retrieve EAV attribute metadata of product
     *
     * @return Data\Eav\AttributeMetadata[]
     */
    public function getProductAttributesMetadata();

    /**
     * Returns all known attributes metadata for a given entity type
     *
     * @param  string $entityType
     * @param  int $attributeSetId
     * @param  int $storeId
     * @return Data\Eav\AttributeMetadata[]
     */
    public function getAllAttributeSetMetadata($entityType, $attributeSetId = 0, $storeId = null);

    /**
     * Retrieve Attribute Metadata
     *
     * @param  string $entityType
     * @param  string $attributeCode
     * @return Data\Eav\AttributeMetadata
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttributeMetadata($entityType, $attributeCode);
}
