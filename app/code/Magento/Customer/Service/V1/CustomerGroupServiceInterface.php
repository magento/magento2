<?php
/**
 * Customer Service Interface
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
namespace Magento\Customer\Service\V1;

use Magento\Exception\InputException;
use Magento\Exception\NoSuchEntityException;

interface CustomerGroupServiceInterface
{
    const NOT_LOGGED_IN_ID          = 0;
    const CUST_GROUP_ALL            = 32000;
    const GROUP_CODE_MAX_LENGTH     = 32;

    /**
     * Retrieve Customer Groups
     *
     * The list of groups can be filtered to exclude the NOT_LOGGED_IN group using the first parameter and/or it can
     * be filtered by tax class.
     *
     * @param boolean $includeNotLoggedIn
     * @param int $taxClassId
     *
     * @return Dto\CustomerGroup[]
     */
    public function getGroups($includeNotLoggedIn = true, $taxClassId = null);

    /**
     * @param Dto\SearchCriteria $searchCriteria
     * @throws InputException if there is a problem with the input
     * @return Dto\SearchResults containing Dto\CustomerGroup objects
     */
    public function searchGroups(Dto\SearchCriteria $searchCriteria);

    /**
     * Get a customer group by group ID.
     *
     * @param int $groupId
     * @throws NoSuchEntityException if $groupId is not found
     * @return Dto\CustomerGroup
     */
    public function getGroup($groupId);

    /**
     * @param int $storeId
     * @throws NoSuchEntityException if default group for $storeId is not found
     * @return Dto\CustomerGroup
     */
    public function getDefaultGroup($storeId);

    /**
     * @param int $groupId
     *
     * @return boolean true, if this group can be deleted
     */
    public function canDelete($groupId);

    /**
     * @param Dto\CustomerGroup $group
     * @throws \Exception if something goes wrong during save
     * @return int customer group ID
     */
    public function saveGroup(Dto\CustomerGroup $group);

    /**
     * @param int $groupId
     * @throws NoSuchEntityException if $groupId is not found
     * @throws \Exception if something goes wrong during delete
     * @return null
     */
    public function deleteGroup($groupId);
}
