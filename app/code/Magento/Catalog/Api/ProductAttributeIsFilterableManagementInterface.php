<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

/**
 * Intended to allow setting 'is_filterable' property for specific attribute as integer value via REST/SOAP API
 *
 * @api
 */
interface ProductAttributeIsFilterableManagementInterface
{
    /**
     * Retrieve 'is_filterable' property for specific attribute as integer
     *
     * @param string $attributeCode
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(string $attributeCode): int;

    /**
     * Set 'is_filterable' property for specific attribute as integer
     *
     * @param string $attributeCode
     * @param int $isFilterable
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function set(string $attributeCode, int $isFilterable): bool;
}
