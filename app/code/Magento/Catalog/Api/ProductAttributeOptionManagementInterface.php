<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

/**
 * @api
 * @since 2.0.0
 */
interface ProductAttributeOptionManagementInterface
{
    /**
     * Retrieve list of attribute options
     *
     * @param string $attributeCode
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]
     * @since 2.0.0
     */
    public function getItems($attributeCode);

    /**
     * Add option to attribute
     *
     * @param string $attributeCode
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @return bool
     * @since 2.0.0
     */
    public function add($attributeCode, $option);

    /**
     * Delete option from attribute
     *
     * @param string $attributeCode
     * @param string $optionId
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @return bool
     * @since 2.0.0
     */
    public function delete($attributeCode, $optionId);
}
