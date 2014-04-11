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

use Magento\Customer\Model\Group as CustomerGroupModel;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\Resource\Group\Collection;
use Magento\Exception\InputException;
use Magento\Exception\NoSuchEntityException;
use Magento\Exception\StateException;
use Magento\Service\V1\Data\Filter;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\Tax\Model\ClassModelFactory as TaxClassModelFactory;

/**
 * Class CustomerGroupService
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerGroupService implements CustomerGroupServiceInterface
{
    /**
     * @var GroupFactory
     */
    private $_groupFactory;

    /**
     * @var Scope Config
     */
    private $_scopeConfig;

    /**
     * @var Data\SearchResultsBuilder
     */
    private $_searchResultsBuilder;

    /**
     * @var Data\CustomerGroupBuilder
     */
    private $_customerGroupBuilder;

    /**
     * @var TaxClassModelFactory
     */
    private $_taxClassModelFactory;

    /**
     * The default tax class id if no tax class id is specified
     */
    const DEFAULT_TAX_CLASS_ID = 3;

    /**
     * @param GroupFactory $groupFactory
     * @param \Magento\App\Config\ScopeConfigInterface $scopeConfig
     * @param Data\SearchResultsBuilder $searchResultsBuilder
     * @param Data\CustomerGroupBuilder $customerGroupBuilder
     * @param TaxClassModelFactory $taxClassModel
     */
    public function __construct(
        GroupFactory $groupFactory,
        \Magento\App\Config\ScopeConfigInterface $scopeConfig,
        Data\SearchResultsBuilder $searchResultsBuilder,
        Data\CustomerGroupBuilder $customerGroupBuilder,
        TaxClassModelFactory $taxClassModelFactory
    ) {
        $this->_groupFactory = $groupFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_searchResultsBuilder = $searchResultsBuilder;
        $this->_customerGroupBuilder = $customerGroupBuilder;
        $this->_taxClassModelFactory = $taxClassModelFactory;
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
            $collection->addFieldToFilter('tax_class_id', $taxClassId);
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
    public function searchGroups(Data\SearchCriteria $searchCriteria)
    {
        $this->_searchResultsBuilder->setSearchCriteria($searchCriteria);

        $groups = array();
        /** @var Collection $collection */
        $collection = $this->_groupFactory->create()->getCollection();
        $this->addFiltersFromRootToCollection($searchCriteria->getAndGroup(), $collection);
        $this->_searchResultsBuilder->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $field => $direction) {
                $field = $this->translateField($field);
                $collection->addOrder($field, $direction == Data\SearchCriteria::SORT_ASC ? 'ASC' : 'DESC');
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var CustomerGroupModel $group */
        foreach ($collection as $group) {
            $this->_customerGroupBuilder->setId(
                $group->getId()
            )->setCode(
                $group->getCode()
            )->setTaxClassId(
                $group->getTaxClassId()
            );
            $groups[] = $this->_customerGroupBuilder->create();
        }
        $this->_searchResultsBuilder->setItems($groups);
        return $this->_searchResultsBuilder->create();
    }

    /**
     * Adds some filters from a root filter group to a collection.
     *
     * @param Data\Search\AndGroup $rootAndGroup
     * @param Collection $collection
     * @return void
     * @throws \Magento\Exception\InputException
     */
    protected function addFiltersFromRootToCollection(Data\Search\AndGroup $rootAndGroup, Collection $collection)
    {
        if (count($rootAndGroup->getAndGroups())) {
            throw new InputException('Only OR groups are supported as nested groups.');
        }

        foreach ($rootAndGroup->getFilters() as $filter) {
            $this->addFilterToCollection($collection, $filter);
        }

        foreach ($rootAndGroup->getOrGroups() as $group) {
            $this->addOrFilterGroupToCollection($collection, $group);
        }
    }

    /**
     * Helper function that adds a filter to the collection
     *
     * @param Collection $collection
     * @param Filter $filter
     * @return void
     */
    protected function addFilterToCollection(Collection $collection, Filter $filter)
    {
        $field = $this->translateField($filter->getField());
        $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
        $collection->addFieldToFilter($field, [$condition => $filter->getValue()]);
    }

    /**
     * Helper function that adds a OrGroup to the collection.
     *
     * @param Collection $collection
     * @param Data\Search\OrGroup $orGroup
     * @return void
     * @throws \Magento\Exception\InputException
     */
    protected function addOrFilterGroupToCollection(Collection $collection, Data\Search\OrGroup $orGroup)
    {
        $fields = [];
        $conditions = [];
        foreach ($orGroup->getFilters() as $filter) {
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
            case 'code':
                return 'customer_group_code';
            case 'id':
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
        $customerGroup = $this->_groupFactory->create();
        $customerGroup->load($groupId);
        // Throw exception if a customer group does not exist
        if (is_null($customerGroup->getId())) {
            throw new NoSuchEntityException('groupId', $groupId);
        }
        $this->_customerGroupBuilder->setId($customerGroup->getId())
            ->setCode($customerGroup->getCode())
            ->setTaxClassId($customerGroup->getTaxClassId());
        return $this->_customerGroupBuilder->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultGroup($storeId)
    {
        try {
            $groupId = $this->_scopeConfig->getValue(
                CustomerGroupModel::XML_PATH_DEFAULT_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } catch (\Magento\Model\Exception $e) {
            throw new NoSuchEntityException('storeId', $storeId);
        }
        try {
            return $this->getGroup($groupId);
        } catch (NoSuchEntityException $e) {
            $e->addField('storeId', $storeId);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function canDelete($groupId)
    {
        $customerGroup = $this->_groupFactory->create();
        $customerGroup->load($groupId);

        // Throw exception if a customer group does not exist
        if (is_null($customerGroup->getId())) {
            throw new NoSuchEntityException('groupId', $groupId);
        }

        return $groupId > 0 && !$customerGroup->usesAsDefault();
    }

    /**
     * {@inheritdoc}
     */
    public function saveGroup(Data\CustomerGroup $group)
    {
        if (!$group->getCode()) {
            throw InputException::create(InputException::INVALID_FIELD_VALUE, 'code', $group->getCode());
        }

        $customerGroup = $this->_groupFactory->create();
        if ($group->getId()) {
            $customerGroup->load($group->getId());

            // Throw exception if the customer group does not exist
            if (is_null($customerGroup->getId())) {
                throw new NoSuchEntityException('id', $group->getId());
            }
        }
        $customerGroup->setCode($group->getCode());

        $taxClassId = $group->getTaxClassId();
        if (!$taxClassId) {
            $taxClassId = self::DEFAULT_TAX_CLASS_ID;
        }
        $this->_verifyTaxClassModel($taxClassId, $group);

        $customerGroup->setTaxClassId($taxClassId);
        try {
            $customerGroup->save();
        } catch (\Magento\Model\Exception $e) {
            /* Would like a better way to determine this error condition but
               difficult to do without imposing more database calls
            */
            if ($e->getMessage() === __('Customer Group already exists.')) {
                $e = new InputException($e->getMessage());
                $e->addError(InputException::INVALID_FIELD_VALUE, 'code', $group->getCode());
                throw $e;
            }
            throw $e;
        }

        return $customerGroup->getId();
    }

    /**
     * Verifies that the tax class model exists and is a customer tax class type.
     *
     * @param int $taxClassId The id of the tax class model to check
     * @param \Magento\Customer\Service\V1\Data\CustomerGroup $group The original group parameters
     * @return void
     * @throws InputException Thrown if the tax class model is invalid
     */
    protected function _verifyTaxClassModel($taxClassId, $group)
    {
        /* Doing this until a Tax Service API is available */
        $taxClassModel = $this->_taxClassModelFactory->create();
        $taxClassModel->load($taxClassId);
        if (is_null($taxClassModel->getId())
            || $taxClassModel->getClassType() !== TaxClassModel::TAX_CLASS_TYPE_CUSTOMER
            ) {
            throw InputException::create(InputException::INVALID_FIELD_VALUE, 'taxClassId', $group->getTaxClassId());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteGroup($groupId)
    {
        if (!$this->canDelete($groupId)) {
            throw new StateException(__("Cannot delete group."));
        }

        // Get group so we can throw an exception if it doesn't exist
        $this->getGroup($groupId);
        $customerGroup = $this->_groupFactory->create();
        $customerGroup->setId($groupId);
        $customerGroup->delete();
        return true;
    }
}
