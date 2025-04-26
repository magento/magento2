<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api;

/**
 * Interface AttributeOptionManagementInterface
 * @api
 * @since 100.0.2
 */
interface AttributeOptionManagementInterface
{
    /**
     * Add an option to attribute
     *
     * @param string $entityType
     * @param string $attributeCode
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function add(string $entityType, string $attributeCode, Data\AttributeOptionInterface $option): string;

    /**
     * Delete option from attribute
     *
     * @param string $entityType
     * @param string $attributeCode
     * @param string $optionId
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(string $entityType, string $attributeCode, string|int $optionId): bool;

    /**
     * Retrieve a list of attribute options
     *
     * @param string $entityType
     * @param string $attributeCode
     *
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]
     *
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getItems(string $entityType, string $attributeCode): array;
}
