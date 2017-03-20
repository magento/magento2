<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\AttributeMetadataInterface;

/**
 * Interface for managing attributes metadata.
 * @api
 */
interface MetadataManagementInterface
{
    /**
     * Check whether attribute is searchable in admin grid and it is allowed
     *
     * @api
     * @param AttributeMetadataInterface $attribute
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canBeSearchableInGrid(AttributeMetadataInterface $attribute);

    /**
     * Check whether attribute is filterable in admin grid and it is allowed
     *
     * @api
     * @param AttributeMetadataInterface $attribute
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canBeFilterableInGrid(AttributeMetadataInterface $attribute);
}
