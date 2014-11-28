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
namespace Magento\Customer\Api;

/**
 * Interface for managing customer groups.
 */
interface GroupManagementInterface
{
    /**
     * Check if customer group can be deleted.
     *
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If group is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isReadonly($id);

    /**
     * Get default customer group.
     *
     * @param int $storeId
     * @return \Magento\Customer\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultGroup($storeId = null);

    /**
     * Get customer group representing customers not logged in.
     *
     * @return \Magento\Customer\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNotLoggedInGroup();

    /**
     * Get all customer groups except group representing customers not logged in.
     *
     * @return \Magento\Customer\Api\Data\GroupInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLoggedInGroups();

    /**
     * Get customer group representing all customers.
     *
     * @return \Magento\Customer\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllCustomersGroup();
}
