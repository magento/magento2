<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api;

interface AttributeRepositoryInterface
{
    /**
     * Retrieve all attributes for entity type
     *
     * @param string $entityTypeCode
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Eav\Api\Data\AttributeSearchResultsInterface
     */
    public function getList($entityTypeCode, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Retrieve specific attribute
     *
     * @param string $entityTypeCode
     * @param string $attributeCode
     * @return \Magento\Eav\Api\Data\AttributeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($entityTypeCode, $attributeCode);

    /**
     * Create attribute data
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @return string
     * @throws \Magento\Framework\Exception\StateException
     */
    public function save(\Magento\Eav\Api\Data\AttributeInterface $attribute);

    /**
     * Delete Attribute
     *
     * @param Data\AttributeInterface $attribute
     * @return bool True if the entity was deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(Data\AttributeInterface $attribute);

    /**
     * Delete Attribute By Id
     *
     * @param int $attributeId
     * @return bool True if the entity was deleted
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($attributeId);
}
