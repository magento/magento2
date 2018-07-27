<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

/**
 * @api
 */
interface ProductAttributeGroupRepositoryInterface
{
    /**
     * Save attribute group
     *
     * @param \Magento\Eav\Api\Data\AttributeGroupInterface $group
     * @return \Magento\Eav\Api\Data\AttributeGroupInterface
     */
    public function save(\Magento\Eav\Api\Data\AttributeGroupInterface $group);

    /**
     * Retrieve list of attribute groups
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Eav\Api\Data\AttributeGroupSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Retrieve attribute group
     *
     * @param int $groupId
     * @return \Magento\Eav\Api\Data\AttributeGroupInterface
     */
    public function get($groupId);

    /**
     * Remove attribute group
     *
     * @param \Magento\Eav\Api\Data\AttributeGroupInterface $group
     * @return bool
     */
    public function delete(\Magento\Eav\Api\Data\AttributeGroupInterface $group);

    /**
     * Remove attribute group by id
     *
     * @param int $groupId
     * @return bool
     */
    public function deleteById($groupId);
}
