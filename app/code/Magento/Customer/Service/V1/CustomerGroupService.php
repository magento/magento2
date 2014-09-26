<?php
/**
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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\StoreManagerInterface;
use Magento\Customer\Model\Group as CustomerGroupModel;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\GroupRegistry;
use Magento\Customer\Model\Resource\Group\Collection;
use Magento\Customer\Service\V1\Data\CustomerGroup;
use Magento\Framework\Service\V1\Data\Search\FilterGroup;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Service\V1\Data\SearchCriteria;
use Magento\Tax\Service\V1\Data\TaxClass;
use Magento\Tax\Service\V1\TaxClassServiceInterface;
use Magento\Framework\Service\V1\Data\SortOrder;

/**
 * Customer service is responsible for customer business workflow encapsulation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerGroupService implements CustomerGroupServiceInterface
{

    const MESSAGE_CUSTOMER_GROUP_ID_IS_NOT_EXPECTED = 'ID is not expected for this request.';

    /**
     * @var GroupFactory
     */
    private $_groupFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var Data\SearchResultsBuilder
     */
    private $_searchResultsBuilder;

    /**
     * @var Data\CustomerGroupBuilder
     */
    private $_customerGroupBuilder;

    /**
     * @var TaxClassServiceInterface
     */
    private $_taxClassService;

    /**
     * @var GroupRegistry
     */
    private $_groupRegistry;

    /**
     * The default tax class id if no tax class id is specified
     */
    const DEFAULT_TAX_CLASS_ID = 3;

    /**
     * @param GroupFactory $groupFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param Data\SearchResultsBuilder $searchResultsBuilder
     * @param Data\CustomerGroupBuilder $customerGroupBuilder
     * @param TaxClassServiceInterface $taxClassService
     * @param GroupRegistry $groupRegistry
     */
    public function __construct(
        GroupFactory $groupFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Data\SearchResultsBuilder $searchResultsBuilder,
        Data\CustomerGroupBuilder $customerGroupBuilder,
        TaxClassServiceInterface $taxClassService,
        GroupRegistry $groupRegistry
    ) {
        $this->_groupFactory = $groupFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_searchResultsBuilder = $searchResultsBuilder;
        $this->_customerGroupBuilder = $customerGroupBuilder;
        $this->_groupRegistry = $groupRegistry;
        $this->_taxClassService = $taxClassService;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups($includeNotLoggedIn = true, $taxClassId = null)
    {
        $groups = array();
        /** @var Collection $collection */
        $collection = $this->_groupFactory->create()->getCollection();
        if (!$includeNotLoggedIn) {
            $collection->setRealGroupsFilter();
        }
        if (!is_null($taxClassId)) {
            $collection->addFieldToFilter(CustomerGroup::TAX_CLASS_ID, $taxClassId);
        }
        /** @var CustomerGroupModel $group */
        foreach ($collection as $group) {
            $this->_customerGroupBuilder->setId($group->getId())
                ->setCode($group->getCode())
                ->setTaxClassId($group->getTaxClassId());
            $groups[] = $this->_customerGroupBuilder->create();
        }
        return $groups;
    }

    /**
     * {@inheritdoc}
     */
    public function searchGroups(SearchCriteria $searchCriteria)
    {
        $this->_searchResultsBuilder->setSearchCriteria($searchCriteria);

        $groups = array();
        /** @var Collection $collection */
        $collection = $this->_groupFactory->create()->getCollection()->addTaxClass();
        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $this->_searchResultsBuilder->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $field = $this->translateField($sortOrder->getField());
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == SearchCriteria::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var CustomerGroupModel $group */
        foreach ($collection as $group) {
            $this->_customerGroupBuilder
                ->setId($group->getId())
                ->setCode($group->getCode())
                ->setTaxClassId($group->getTaxClassId())
                ->setTaxClassName($group->getClassName());
            $groups[] = $this->_customerGroupBuilder->create();
        }
        $this->_searchResultsBuilder->setItems($groups);
        return $this->_searchResultsBuilder->create();
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $this->translateField($filter->getField());
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Translates a field name to a DB column name for use in collection queries.
     *
     * @param string $field a field name that should be translated to a DB column name.
     * @return string
     */
    protected function translateField($field)
    {
        switch ($field) {
            case CustomerGroup::CODE:
                return 'customer_group_code';
            case CustomerGroup::ID:
                return 'customer_group_id';
            default:
                return $field;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup($groupId)
    {
        $customerGroup = $this->_groupRegistry->retrieve($groupId);
        $this->_customerGroupBuilder->setId($customerGroup->getId())
            ->setCode($customerGroup->getCode())
            ->setTaxClassId($customerGroup->getTaxClassId());
        return $this->_customerGroupBuilder->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultGroup($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->_storeManager->getStore()->getCode();
        }
        try {
            $groupId = $this->_scopeConfig->getValue(
                CustomerGroupModel::XML_PATH_DEFAULT_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } catch (\Magento\Framework\App\InitException $e) {
            throw NoSuchEntityException::singleField('storeId', $storeId);
        }
        try {
            return $this->getGroup($groupId);
        } catch (NoSuchEntityException $e) {
            throw NoSuchEntityException::doubleField('groupId', $groupId, 'storeId', $storeId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function canDelete($groupId)
    {
        $customerGroup = $this->_groupRegistry->retrieve($groupId);
        return $groupId > 0 && !$customerGroup->usesAsDefault();
    }

    /**
     * {@inheritdoc}
     */
    public function createGroup(Data\CustomerGroup $group)
    {
        if ($group->getId()) {
            throw new InputException(self::MESSAGE_CUSTOMER_GROUP_ID_IS_NOT_EXPECTED);
        }

        if (!$group->getCode()) {
            throw InputException::invalidFieldValue('code', $group->getCode());
        }

        /** @var /Magento/Customer/Model/Group $customerGroup */
        $customerGroup = $this->_groupFactory->create();
        $customerGroup->setCode($group->getCode());

        /** @var int $taxClassId */
        $taxClassId = $group->getTaxClassId();
        if (!$taxClassId) {
            $taxClassId = self::DEFAULT_TAX_CLASS_ID;
        }
        $this->_verifyTaxClassModel($taxClassId, $group);

        $customerGroup->setTaxClassId($taxClassId);
        try {
            $customerGroup->save();
        } catch (\Magento\Framework\Model\Exception $e) {
            /**
             * Would like a better way to determine this error condition but
             *  difficult to do without imposing more database calls
             */
            if ($e->getMessage() === __('Customer Group already exists.')) {
                throw new InvalidTransitionException('Customer Group already exists.');
            }
            throw $e;
        }

        return $customerGroup->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function updateGroup($groupId, Data\CustomerGroup $group)
    {
        if (!$group->getCode()) {
            throw InputException::invalidFieldValue('code', $group->getCode());
        }

        /** @var /Magento/Customer/Model/Group $customerGroup */
        $customerGroup = null;
        try {
            $customerGroup = $this->_groupRegistry->retrieve($groupId);
        } catch (NoSuchEntityException $e) {
            throw NoSuchEntityException::singleField('id', $groupId);
        }

        $customerGroup->setCode($group->getCode());

        /** @var int $taxClassId */
        $taxClassId = $group->getTaxClassId();
        if (!$taxClassId) {
            $taxClassId = self::DEFAULT_TAX_CLASS_ID;
        }
        $this->_verifyTaxClassModel($taxClassId, $group);

        $customerGroup->setTaxClassId($taxClassId);
        try {
            $customerGroup->save();
        } catch (\Magento\Framework\Model\Exception $e) {
            /**
             * Would like a better way to determine this error condition but
             *  difficult to do without imposing more database calls
             */
            if ($e->getMessage() === __('Customer Group already exists.')) {
                throw new InvalidTransitionException('Customer Group already exists.');
            }
            throw $e;
        }

        return true;
    }

    /**
     * Verifies that the tax class model exists and is a customer tax class type.
     *
     * @param int $taxClassId The id of the tax class model to check
     * @param CustomerGroup $group The original group parameters
     * @return void
     * @throws InputException Thrown if the tax class model is invalid
     */
    protected function _verifyTaxClassModel($taxClassId, $group)
    {
        try {
            /* @var TaxClass $taxClassData */
            $taxClassData = $this->_taxClassService->getTaxClass($taxClassId);
        } catch (NoSuchEntityException $e) {
            throw InputException::invalidFieldValue('taxClassId', $group->getTaxClassId());
        }
        if ($taxClassData->getClassType() !== TaxClassServiceInterface::TYPE_CUSTOMER) {
            throw InputException::invalidFieldValue('taxClassId', $group->getTaxClassId());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteGroup($groupId)
    {
        if (!$this->canDelete($groupId)) {
            throw new StateException('Cannot delete group.');
        }

        // Get group so we can throw an exception if it doesn't exist
        $this->getGroup($groupId);
        $customerGroup = $this->_groupFactory->create();
        $customerGroup->setId($groupId);
        $customerGroup->delete();
        $this->_groupRegistry->remove($groupId);
        return true;
    }
}
