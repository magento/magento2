<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;

/**
 * Sales rule CRUD class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleRepository implements \Magento\SalesRule\Api\RuleRepositoryInterface
{
    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Magento\SalesRule\Api\Data\RuleInterfaceFactory
     */
    protected $ruleDataFactory;

    /**
     * @var \Magento\SalesRule\Api\Data\ConditionInterfaceFactory
     */
    protected $conditionDataFactory;

    /**
     * @var \Magento\SalesRule\Model\Converter\ToDataModel
     */
    protected $toDataModelConverter;

    /**
     * @var \Magento\SalesRule\Model\Converter\ToModel
     */
    protected $toModelConverter;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\SalesRule\Api\Data\RuleSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * RuleRepository constructor.
     * @param RuleFactory $ruleFactory
     * @param \Magento\SalesRule\Api\Data\RuleInterfaceFactory $ruleDataFactory
     * @param \Magento\SalesRule\Api\Data\ConditionInterfaceFactory $conditionDataFactory
     * @param Converter\ToDataModel $toDataModelConverter
     * @param Converter\ToModel $toModelConverter
     * @param \Magento\SalesRule\Api\Data\RuleSearchResultInterfaceFactory $searchResultFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Api\Data\RuleInterfaceFactory $ruleDataFactory,
        \Magento\SalesRule\Api\Data\ConditionInterfaceFactory $conditionDataFactory,
        \Magento\SalesRule\Model\Converter\ToDataModel $toDataModelConverter,
        \Magento\SalesRule\Model\Converter\ToModel $toModelConverter,
        \Magento\SalesRule\Api\Data\RuleSearchResultInterfaceFactory $searchResultFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->ruleDataFactory = $ruleDataFactory;
        $this->conditionDataFactory = $conditionDataFactory;
        $this->toDataModelConverter = $toDataModelConverter;
        $this->toModelConverter = $toModelConverter;
        $this->searchResultFactory = $searchResultFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * @inheritdoc
     */
    public function save(\Magento\SalesRule\Api\Data\RuleInterface $rule)
    {
        $model = $this->toModelConverter->toModel($rule);
        $model->save();
        $model->load($model->getId());
        $model->getStoreLabels();
        return $this->toDataModelConverter->toDataModel($model);
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        $model = $this->ruleFactory->create()
            ->load($id);

        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException();
        }

        $model->getStoreLabels();
        $dataModel = $this->toDataModelConverter->toDataModel($model);
        return $dataModel;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection $collection */
        $collection = $this->ruleCollectionFactory->create();
        $ruleInterfaceName = \Magento\SalesRule\Api\Data\RuleInterface::class;
        $this->extensionAttributesJoinProcessor->process($collection, $ruleInterfaceName);

        $this->collectionProcessor->process($searchCriteria, $collection);
        $rules = [];
        /** @var \Magento\SalesRule\Model\Rule $ruleModel */
        foreach ($collection->getItems() as $ruleModel) {
            $ruleModel->load($ruleModel->getId());
            $ruleModel->getStoreLabels();
            $rules[] = $this->toDataModelConverter->toDataModel($ruleModel);
        }

        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($rules);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete sales rule by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id)
    {
        $model = $this->ruleFactory->create()->load($id);

        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException();
        }
        $model->delete();
        return true;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @deprecated 100.2.0
     * @return void
     */
    protected function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        Collection $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 100.2.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
            );
        }
        return $this->collectionProcessor;
    }
}
