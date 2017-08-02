<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\ResourceModel;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Tax\Api\Data\TaxClassInterface;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Customer\Api\Data\GroupExtensionInterface;

/**
 * Customer group CRUD class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class GroupRepository implements \Magento\Customer\Api\GroupRepositoryInterface
{
    /**
     * The default tax class id if no tax class id is specified
     */
    const DEFAULT_TAX_CLASS_ID = 3;

    /**
     * @var \Magento\Customer\Model\GroupRegistry
     * @since 2.0.0
     */
    protected $groupRegistry;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     * @since 2.0.0
     */
    protected $groupFactory;

    /**
     * @var \Magento\Customer\Api\Data\GroupInterfaceFactory
     * @since 2.0.0
     */
    protected $groupDataFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group
     * @since 2.0.0
     */
    protected $groupResourceModel;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     * @since 2.0.0
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Customer\Api\Data\GroupSearchResultsInterfaceFactory
     * @since 2.0.0
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     * @since 2.0.0
     */
    private $taxClassRepository;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     * @since 2.0.0
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     * @since 2.2.0
     */
    private $collectionProcessor;

    /**
     * @param \Magento\Customer\Model\GroupRegistry $groupRegistry
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magento\Customer\Api\Data\GroupInterfaceFactory $groupDataFactory
     * @param \Magento\Customer\Model\ResourceModel\Group $groupResourceModel
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Customer\Api\Data\GroupSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepositoryInterface
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Customer\Model\GroupRegistry $groupRegistry,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\Customer\Api\Data\GroupInterfaceFactory $groupDataFactory,
        \Magento\Customer\Model\ResourceModel\Group $groupResourceModel,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Customer\Api\Data\GroupSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepositoryInterface,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->groupRegistry = $groupRegistry;
        $this->groupFactory = $groupFactory;
        $this->groupDataFactory = $groupDataFactory;
        $this->groupResourceModel = $groupResourceModel;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->taxClassRepository = $taxClassRepositoryInterface;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function save(\Magento\Customer\Api\Data\GroupInterface $group)
    {
        $this->_validate($group);

        /** @var \Magento\Customer\Model\Group $groupModel */
        $groupModel = null;
        if ($group->getId() || (string)$group->getId() === '0') {
            $this->_verifyTaxClassModel($group->getTaxClassId(), $group);
            $groupModel = $this->groupRegistry->retrieve($group->getId());
            $groupDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
                $group,
                \Magento\Customer\Api\Data\GroupInterface::class
            );
            foreach ($groupDataAttributes as $attributeCode => $attributeData) {
                $groupModel->setDataUsingMethod($attributeCode, $attributeData);
            }
        } else {
            $groupModel = $this->groupFactory->create();
            $groupModel->setCode($group->getCode());

            $taxClassId = $group->getTaxClassId() ?: self::DEFAULT_TAX_CLASS_ID;
            $this->_verifyTaxClassModel($taxClassId, $group);
            $groupModel->setTaxClassId($taxClassId);
        }

        try {
            $this->groupResourceModel->save($groupModel);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            /**
             * Would like a better way to determine this error condition but
             *  difficult to do without imposing more database calls
             */
            if ($e->getMessage() == (string)__('Customer Group already exists.')) {
                throw new InvalidTransitionException(__('Customer Group already exists.'));
            }
            throw $e;
        }

        $this->groupRegistry->remove($groupModel->getId());

        $groupDataObject = $this->groupDataFactory->create()
            ->setId($groupModel->getId())
            ->setCode($groupModel->getCode())
            ->setTaxClassId($groupModel->getTaxClassId())
            ->setTaxClassName($groupModel->getTaxClassName());
        return $groupDataObject;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getById($id)
    {
        $groupModel = $this->groupRegistry->retrieve($id);
        $groupDataObject = $this->groupDataFactory->create()
            ->setId($groupModel->getId())
            ->setCode($groupModel->getCode())
            ->setTaxClassId($groupModel->getTaxClassId())
            ->setTaxClassName($groupModel->getTaxClassName());
        return $groupDataObject;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Magento\Customer\Model\ResourceModel\Group\Collection $collection */
        $collection = $this->groupFactory->create()->getCollection();
        $groupInterfaceName = \Magento\Customer\Api\Data\GroupInterface::class;
        $this->extensionAttributesJoinProcessor->process($collection, $groupInterfaceName);
        $collection->addTaxClass();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Magento\Customer\Api\Data\GroupInterface[] $groups */
        $groups = [];
        /** @var \Magento\Customer\Model\Group $group */
        foreach ($collection as $group) {
            /** @var \Magento\Customer\Api\Data\GroupInterface $groupDataObject */
            $groupDataObject = $this->groupDataFactory->create()
                ->setId($group->getId())
                ->setCode($group->getCode())
                ->setTaxClassId($group->getTaxClassId())
                ->setTaxClassName($group->getTaxClassName());
            $data = $group->getData();
            $data = $this->extensionAttributesJoinProcessor->extractExtensionAttributes($groupInterfaceName, $data);
            if (isset($data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY])
                && ($data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY] instanceof GroupExtensionInterface)
            ) {
                $groupDataObject->setExtensionAttributes($data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
            }
            $groups[] = $groupDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($groups);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @deprecated 2.2.0
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @since 2.0.0
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
     * @deprecated 2.2.0
     * @param string $field a field name that should be translated to a DB column name.
     * @return string
     * @since 2.0.0
     */
    protected function translateField($field)
    {
        switch ($field) {
            case GroupInterface::CODE:
                return 'customer_group_code';
            case GroupInterface::ID:
                return 'customer_group_id';
            case GroupInterface::TAX_CLASS_NAME:
                return 'class_name';
            default:
                return $field;
        }
    }

    /**
     * Delete customer group.
     *
     * @param GroupInterface $group
     * @return bool true on success
     * @throws \Magento\Framework\Exception\StateException If customer group cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function delete(GroupInterface $group)
    {
        return $this->deleteById($group->getId());
    }

    /**
     * Delete customer group by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException If customer group cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function deleteById($id)
    {
        $groupModel = $this->groupRegistry->retrieve($id);

        if ($id <= 0 || $groupModel->usesAsDefault()) {
            throw new \Magento\Framework\Exception\StateException(__('Cannot delete group.'));
        }

        $groupModel->delete();
        $this->groupRegistry->remove($id);
        return true;
    }

    /**
     * Validate group values.
     *
     * @param \Magento\Customer\Api\Data\GroupInterface $group
     * @throws InputException
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    private function _validate($group)
    {
        $exception = new InputException();
        if (!\Zend_Validate::is($group->getCode(), 'NotEmpty')) {
            $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'code']));
        }

        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }

    /**
     * Verifies that the tax class model exists and is a customer tax class type.
     *
     * @param int $taxClassId The id of the tax class model to check
     * @param \Magento\Customer\Api\Data\GroupInterface $group The original group parameters
     * @return void
     * @throws InputException Thrown if the tax class model is invalid
     * @since 2.0.0
     */
    protected function _verifyTaxClassModel($taxClassId, $group)
    {
        try {
            /* @var TaxClassInterface $taxClassData */
            $taxClassData = $this->taxClassRepository->get($taxClassId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw InputException::invalidFieldValue('taxClassId', $group->getTaxClassId());
        }
        if ($taxClassData->getClassType() !== TaxClassManagementInterface::TYPE_CUSTOMER) {
            throw InputException::invalidFieldValue('taxClassId', $group->getTaxClassId());
        }
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 2.2.0
     * @return CollectionProcessorInterface
     * @since 2.2.0
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Customer\Model\Api\SearchCriteria\GroupCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}
