<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Eav\Api;

interface AttributeGroupRepositoryInterface
{
    /**
     * Save attribute group
     *
     * @param \Magento\Eav\Api\Data\AttributeGroupInterface $group
     * @return \Magento\Eav\Api\Data\AttributeGroupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function save(\Magento\Eav\Api\Data\AttributeGroupInterface $group);

    /**
     * Retrieve attribute group
     *
     * @param int $groupId
     * @return \Magento\Eav\Api\Data\AttributeGroupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($groupId);

    /**
     * Retrieve list of attribute groups
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Eav\Api\Data\AttributeGroupSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Remove attribute group by id
     *
     * @param int $groupId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($groupId);

    /**
     * Remove attribute group
     *
     * @param \Magento\Eav\Api\Data\AttributeGroupInterface $group
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Magento\Eav\Api\Data\AttributeGroupInterface $group);
}
