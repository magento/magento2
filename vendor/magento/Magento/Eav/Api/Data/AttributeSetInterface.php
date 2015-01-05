<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Eav\Api\Data;

interface AttributeSetInterface
{
    /**
     * Get attribute set ID
     *
     * @return int|null
     */
    public function getAttributeSetId();

    /**
     * Get attribute set name
     *
     * @return string
     */
    public function getAttributeSetName();

    /**
     * Get attribute set sort order index
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Get attribute set entity type id
     *
     * @return int|null
     */
    public function getEntityTypeId();
}
