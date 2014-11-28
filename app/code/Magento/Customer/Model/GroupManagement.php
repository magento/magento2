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

namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\CollectionBuilder\FilterBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\StoreManagerInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\Data\GroupDataBuilder;
use Magento\Customer\Model\GroupFactory;

class GroupManagement implements \Magento\Customer\Api\GroupManagementInterface
{
    const XML_PATH_DEFAULT_ID = 'customer/create_account/default_group';

    const NOT_LOGGED_IN_ID = 0;

    const CUST_GROUP_ALL = 32000;

    const GROUP_CODE_MAX_LENGTH = 32;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var GroupFactory
     */
    protected $groupFactory;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var GroupDataBuilder
     */
    protected $groupBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param GroupFactory $groupFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupDataBuilder $groupBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        GroupFactory $groupFactory,
        GroupRepositoryInterface $groupRepository,
        GroupDataBuilder $groupBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->groupFactory = $groupFactory;
        $this->groupRepository = $groupRepository;
        $this->groupBuilder = $groupBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadonly($groupId)
    {
        /** @var \Magento\Customer\Model\Group $group */
        $group = $this->groupFactory->create();
        $group->load($groupId);
        if (is_null($group->getId())) {
            throw NoSuchEntityException::singleField('groupId', $groupId);
        }
        return $groupId == self::NOT_LOGGED_IN_ID || $group->usesAsDefault();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultGroup($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->storeManager->getStore()->getCode();
        }
        try {
            $groupId = $this->scopeConfig->getValue(
                self::XML_PATH_DEFAULT_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } catch (\Magento\Framework\App\InitException $e) {
            throw NoSuchEntityException::singleField('storeId', $storeId);
        }
        try {
            return $this->groupRepository->getById($groupId);
        } catch (NoSuchEntityException $e) {
            throw NoSuchEntityException::doubleField('groupId', $groupId, 'storeId', $storeId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNotLoggedInGroup()
    {
        return $this->groupRepository->getById(self::NOT_LOGGED_IN_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getLoggedInGroups()
    {
        $notLoggedInFilter[] = $this->filterBuilder
            ->setField(GroupInterface::ID)
            ->setConditionType('neq')
            ->setValue(self::NOT_LOGGED_IN_ID)
            ->create();
        $groupAll[] = $this->filterBuilder
            ->setField(GroupInterface::ID)
            ->setConditionType('neq')
            ->setValue(self::CUST_GROUP_ALL)
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter($notLoggedInFilter)
            ->addFilter($groupAll)
            ->create();
        return $this->groupRepository->getList($searchCriteria)->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllCustomersGroup()
    {
        return $this->groupBuilder->setId(self::CUST_GROUP_ALL)
            ->create();
    }
}
