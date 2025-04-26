<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface AttributeRepositoryInterface
 * @api
 * @since 100.0.2
 */
interface AttributeRepositoryInterface
{
    /**
     * Retrieve all attributes for entity type
     *
     * @param string $entityTypeCode
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Eav\Api\Data\AttributeSearchResultsInterface
     */
    public function getList(
        string $entityTypeCode,
        SearchCriteriaInterface $searchCriteria
    ): Data\AttributeSearchResultsInterface;

    /**
     * Retrieve specific attribute
     *
     * @param string $entityTypeCode
     * @param string $attributeCode
     *
     * @return \Magento\Eav\Api\Data\AttributeInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(string $entityTypeCode, string $attributeCode): Data\AttributeInterface;

    /**
     * Create attribute data
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     *
     * @return \Magento\Eav\Api\Data\AttributeInterface
     *
     * @throws \Magento\Framework\Exception\StateException
     */
    public function save(AttributeInterface $attribute): AttributeInterface;

    /**
     * Delete Attribute
     *
     * @param Data\AttributeInterface $attribute
     * @return bool True if the entity was deleted
     *
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(Data\AttributeInterface $attribute): bool;

    /**
     * Delete Attribute By Id
     *
     * @param int|string $attributeId
     * @return bool True if the entity was deleted
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById(int|string $attributeId): bool;
}
