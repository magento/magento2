<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\SalesRule\Api\Data\ConditionInterfaceFactory;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\Data\RuleInterfaceFactory;
use Magento\SalesRule\Api\Data\RuleSearchResultInterfaceFactory;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Converter\ToDataModel as ToDataModelConverter;
use Magento\SalesRule\Model\Converter\ToModel as ToModelConverter;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection as RuleCollection;
use Magento\SalesRule\Model\Rule as ModelRule;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

/**
 * Sales rule CRUD class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleRepository implements RuleRepositoryInterface
{
    /**
     * RuleRepository constructor.
     * @param RuleFactory $ruleFactory
     * @param RuleInterfaceFactory $ruleDataFactory
     * @param ConditionInterfaceFactory $conditionDataFactory
     * @param ToDataModelConverter $toDataModelConverter
     * @param ToModelConverter $toModelConverter
     * @param RuleSearchResultInterfaceFactory $searchResultFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        protected readonly RuleFactory $ruleFactory,
        protected readonly RuleInterfaceFactory $ruleDataFactory,
        protected readonly ConditionInterfaceFactory $conditionDataFactory,
        protected readonly ToDataModelConverter $toDataModelConverter,
        protected readonly ToModelConverter $toModelConverter,
        protected readonly RuleSearchResultInterfaceFactory $searchResultFactory,
        protected readonly JoinProcessorInterface $extensionAttributesJoinProcessor,
        protected readonly RuleCollectionFactory $ruleCollectionFactory,
        protected readonly DataObjectProcessor $dataObjectProcessor,
        private ?CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * @inheritdoc
     */
    public function save(RuleInterface $rule)
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
            throw new NoSuchEntityException();
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
        /** @var RuleCollection $collection */
        $collection = $this->ruleCollectionFactory->create();
        $ruleInterfaceName = RuleInterface::class;
        $this->extensionAttributesJoinProcessor->process($collection, $ruleInterfaceName);

        $this->collectionProcessor->process($searchCriteria, $collection);
        $rules = [];
        /** @var ModelRule $ruleModel */
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
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($id)
    {
        $model = $this->ruleFactory->create()->load($id);

        if (!$model->getId()) {
            throw new NoSuchEntityException();
        }
        $model->delete();
        return true;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @deprecated 101.0.0
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
     * @deprecated 101.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = ObjectManager::getInstance()->get(
                CollectionProcessorInterface::class
            );
        }
        return $this->collectionProcessor;
    }
}
