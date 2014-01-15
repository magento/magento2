<?php
/**
 * Customer service is responsible for customer business workflow encapsulation
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

use Magento\Customer\Service\V1\Dto\SearchCriteria;
use Magento\Customer\Service\Entity\V1\Exception;

class CustomerGroupService implements CustomerGroupServiceInterface
{
    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    private $_groupFactory;

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    private $_storeConfig;

    /**
     * @var \Magento\Customer\Service\V1\Dto\SearchResultsBuilder
     */
    private $_searchResultsBuilder;

    /**
     * @var \Magento\Customer\Service\V1\Dto\CustomerGroupBuilder
     */
    private $_customerGroupBuilder;

    /**
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\Customer\Service\V1\Dto\SearchResultsBuilder $searchResultsBuilder
     * @param \Magento\Customer\Service\V1\Dto\CustomerGroupBuilder $customerGroupBuilder
     */
    public function __construct(
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\Core\Model\Store\Config $storeConfig,
        Dto\SearchResultsBuilder $searchResultsBuilder,
        Dto\CustomerGroupBuilder $customerGroupBuilder
    ) {
        $this->_groupFactory = $groupFactory;
        $this->_storeConfig = $storeConfig;
        $this->_searchResultsBuilder = $searchResultsBuilder;
        $this->_customerGroupBuilder = $customerGroupBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getGroups($includeNotLoggedIn = true, $taxClassId = null)
    {
        $groups = array();
        /** @var \Magento\Customer\Model\Resource\Group\Collection $collection */
        $collection = $this->_groupFactory->create()->getCollection();
        if (!$includeNotLoggedIn) {
            $collection->setRealGroupsFilter();
        }
        if (!is_null($taxClassId)) {
            $collection->addFieldToFilter('tax_class_id', $taxClassId);
        }
        /** @var \Magento\Customer\Model\Group $group */
        foreach ($collection as $group) {
            $this->_customerGroupBuilder->setId($group->getId())
                ->setCode($group->getCode())
                ->setTaxClassId($group->getTaxClassId());
            $groups[] = $this->_customerGroupBuilder->create();
        }
        return $groups;
    }

    /**
     * @inheritdoc
     */
    public function searchGroups(SearchCriteria $searchCriteria)
    {
        $this->_searchResultsBuilder->setSearchCriteria($searchCriteria);

        $groups = array();
        /** @var \Magento\Customer\Model\Resource\Group\Collection $collection */
        $collection = $this->_groupFactory->create()->getCollection();
        foreach ($searchCriteria->getFilters() as $filter) {
            $collection->addFilter($filter->getField(), $filter->getValue(), $filter->getConditionType());
        }
        $this->_searchResultsBuilder->setTotalCount($collection->getSize());
        foreach ($searchCriteria->getSortOrders() as $field => $direction) {
            switch($field) {
                case 'id' :
                    $field = 'customer_group_id';
                    break;
                case 'code':
                    $field = 'customer_group_code';
                    break;
                case "tax_class_id":
                default:
                    break;
            }
            $collection->addOrder($field, $direction == SearchCriteria::SORT_ASC ? 'ASC' : 'DESC');
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var \Magento\Customer\Model\Group $group */
        foreach ($collection as $group) {
            $this->_customerGroupBuilder->setId($group->getId())
                ->setCode($group->getCode())
                ->setTaxClassId($group->getTaxClassId());
            $groups[] = $this->_customerGroupBuilder->create();
        }
        $this->_searchResultsBuilder->setItems($groups);
        return $this->_searchResultsBuilder->create();
    }

    /**
     * @inheritdoc
     */
    public function getGroup($groupId)
    {
        $customerGroup = $this->_groupFactory->create();
        $customerGroup->load($groupId);
        // Throw exception if a customer group does not exist
        if (is_null($customerGroup->getId())) {
            throw new Exception(__('groupId ' . $groupId . ' does not exist.'));
        }
        $this->_customerGroupBuilder->setId($customerGroup->getId())
            ->setCode($customerGroup->getCode())
            ->setTaxClassId($customerGroup->getTaxClassId());
        return $this->_customerGroupBuilder->create();
    }

    /**
     * @inheritdoc
     */
    public function getDefaultGroup($storeId)
    {
        $groupId = $this->_storeConfig->getConfig(\Magento\Customer\Model\Group::XML_PATH_DEFAULT_ID, $storeId);
        return $this->getGroup($groupId);
    }

    /**
     * @inheritdoc
     */
    public function canDelete($groupId)
    {
        $customerGroup = $this->_groupFactory->create();
        $customerGroup->load($groupId);
        return $groupId > 0 && !$customerGroup->usesAsDefault();
    }

    /**
     * @inheritdoc
     */
    public function saveGroup(Dto\CustomerGroup $group)
    {
        $customerGroup = $this->_groupFactory->create();
        if ($group->getId()) {
            $customerGroup->load($group->getId());
        }
        $customerGroup->setCode($group->getCode());
        $customerGroup->setTaxClassId($group->getTaxClassId());
        $customerGroup->save();
        return $customerGroup->getId();
    }

    /**
     * @inheritdoc
     */
    public function deleteGroup($groupId)
    {
        try {
            // Get group so we can throw an exception if it doesn't exist
            $this->getGroup($groupId);
            $customerGroup = $this->_groupFactory->create();
            $customerGroup->setId($groupId);
            $customerGroup->delete();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
