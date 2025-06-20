<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\TaxClass;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException as ModelException;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\ValidateException;
use Magento\Framework\Validator\ValidatorChain;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\ClassModelRegistry;
use Magento\Tax\Model\ResourceModel\TaxClass\Collection as TaxClassCollection;
use Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory as TaxClassCollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Repository implements \Magento\Tax\Api\TaxClassRepositoryInterface
{
    public const CLASS_ID_NOT_ALLOWED = 'class_id is not expected for this request.';

    /**
     * @var TaxClassCollectionFactory
     */
    protected $taxClassCollectionFactory;

    /**
     * @var \Magento\Tax\Api\Data\TaxClassSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var ClassModelRegistry
     */
    protected $classModelRegistry;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Tax\Model\ResourceModel\TaxClass
     */
    protected $taxClassResource;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $joinProcessor;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param TaxClassCollectionFactory $taxClassCollectionFactory
     * @param \Magento\Tax\Api\Data\TaxClassSearchResultsInterfaceFactory $searchResultsFactory
     * @param ClassModelRegistry $classModelRegistry
     * @param \Magento\Tax\Model\ResourceModel\TaxClass $taxClassResource
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        TaxClassCollectionFactory $taxClassCollectionFactory,
        \Magento\Tax\Api\Data\TaxClassSearchResultsInterfaceFactory $searchResultsFactory,
        ClassModelRegistry $classModelRegistry,
        \Magento\Tax\Model\ResourceModel\TaxClass $taxClassResource,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
        ?CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->taxClassCollectionFactory = $taxClassCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->classModelRegistry = $classModelRegistry;
        $this->taxClassResource = $taxClassResource;
        $this->joinProcessor = $joinProcessor;
        $this->collectionProcessor = $collectionProcessor
            ?: ObjectManager::getInstance()->get(CollectionProcessor::class);
    }

    /**
     * @inheritdoc
     */
    public function save(\Magento\Tax\Api\Data\TaxClassInterface $taxClass)
    {
        if ($taxClass->getClassId()) {
            $originalTaxClassModel = $this->get($taxClass->getClassId());

            /* should not be allowed to switch the tax class type */
            if ($originalTaxClassModel->getClassType() !== $taxClass->getClassType()) {
                throw new InputException(__('Updating classType is not allowed.'));
            }
        }
        $this->validateTaxClassData($taxClass);
        try {
            $this->taxClassResource->save($taxClass);
        } catch (ModelException $e) {
            if (strpos($e->getMessage(), (string)__('Class name and class type')) !== false) {
                throw new InputException(
                    __(
                        'A class with the same name already exists for ClassType %1.',
                        $taxClass->getClassType()
                    )
                );
            } else {
                throw $e;
            }
        }
        $this->classModelRegistry->registerTaxClass($taxClass);
        return $taxClass->getClassId();
    }

    /**
     * @inheritdoc
     */
    public function get($taxClassId)
    {
        return $this->classModelRegistry->retrieve($taxClassId);
    }

    /**
     * @inheritdoc
     */
    public function delete(\Magento\Tax\Api\Data\TaxClassInterface $taxClass)
    {
        $taxClassId = $taxClass->getClassId();
        try {
            $this->taxClassResource->delete($taxClass);
        } catch (CouldNotDeleteException $e) {
            throw $e;
        } catch (\Exception $e) {
            return false;
        }
        $this->classModelRegistry->remove($taxClassId);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($taxClassId)
    {
        $taxClassModel = $this->get($taxClassId);
        return $this->delete($taxClassModel);
    }

    /**
     * Validate TaxClass Data
     *
     * @param \Magento\Tax\Api\Data\TaxClassInterface $taxClass
     * @return void
     * @throws InputException|ValidateException
     */
    protected function validateTaxClassData(\Magento\Tax\Api\Data\TaxClassInterface $taxClass)
    {
        $exception = new InputException();

        if (!ValidatorChain::is(trim($taxClass->getClassName() ?? ''), NotEmpty::class)) {
            $exception->addError(
                __('"%fieldName" is required. Enter and try again.', ['fieldName' => ClassModel::KEY_NAME])
            );
        }

        $classType = $taxClass->getClassType();
        if (!ValidatorChain::is($classType !== null ? trim($classType) : '', NotEmpty::class)) {
            $exception->addError(
                __('"%fieldName" is required. Enter and try again.', ['fieldName' => ClassModel::KEY_TYPE])
            );
        } elseif ($classType !== TaxClassManagementInterface::TYPE_CUSTOMER
            && $classType !== TaxClassManagementInterface::TYPE_PRODUCT
        ) {
            $exception->addError(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['fieldName' => ClassModel::KEY_TYPE, 'value' => $classType]
                )
            );
        }

        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        /** @var TaxClassCollection $collection */
        $collection = $this->taxClassCollectionFactory->create();
        $this->joinProcessor->process($collection);
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param TaxClassCollection $collection
     *
     * @return void
     * @deprecated 100.2.0
     * @see we don't recommend this approach anymore
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, TaxClassCollection $collection)
    {
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
}
