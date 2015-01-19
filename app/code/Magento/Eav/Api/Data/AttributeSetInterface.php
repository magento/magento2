<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
