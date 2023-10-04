<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
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
