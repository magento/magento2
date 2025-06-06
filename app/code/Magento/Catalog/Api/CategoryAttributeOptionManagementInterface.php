<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api;

/**
 * Interface RepositoryInterface must be implemented in new model
 * @api
 * @since 100.0.2
 */
interface CategoryAttributeOptionManagementInterface
{
    /**
     * Retrieve list of attribute options
     *
     * @param string $attributeCode
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]
     */
    public function getItems($attributeCode);
}
