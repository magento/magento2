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

namespace Magento\Tax\Service\V1;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Exception as ModelException;
use Magento\Framework\Service\V1\Data\Search\FilterGroup;
use Magento\Framework\Service\V1\Data\SearchCriteria;
use Magento\Tax\Model\ClassModelFactory as TaxClassModelFactory;
use Magento\Tax\Model\Converter;
use Magento\Tax\Model\Resource\TaxClass\Collection as TaxClassCollection;
use Magento\Tax\Model\Resource\TaxClass\CollectionFactory as TaxClassCollectionFactory;
use Magento\Tax\Service\V1\Data\SearchResultsBuilder;
use Magento\Tax\Service\V1\Data\TaxClass as TaxClassDataObject;

/**
 * Tax class service.
 */
class TaxClassService implements TaxClassServiceInterface
{
    /**
     * @var TaxClassCollectionFactory
     */
    protected $taxClassCollectionFactory;

    /**
     * @var SearchResultsBuilder
     */
    protected $searchResultsBuilder;

    /**
     * @var Converter
     */
    protected $converter;

    /**
     * @var TaxClassModelFactory
     */
    protected $taxClassModelFactory;

    const CLASS_ID_NOT_ALLOWED = 'class_id is not expected for this request.';

    /**
     * Initialize dependencies.
     *
     * @param TaxClassCollectionFactory $taxClassCollectionFactory
     * @param TaxClassModelFactory $taxClassModelFactory
     * @param SearchResultsBuilder $searchResultsBuilder
     * @param Converter $converter
     */
    public function __construct(
        TaxClassCollectionFactory $taxClassCollectionFactory,
        TaxClassModelFactory $taxClassModelFactory,
        SearchResultsBuilder $searchResultsBuilder,
        Converter $converter
    ) {
        $this->taxClassCollectionFactory = $taxClassCollectionFactory;
        $this->taxClassModelFactory = $taxClassModelFactory;
        $this->searchResultsBuilder = $searchResultsBuilder;
        $this->converter = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function createTaxClass(TaxClassDataObject $taxClass)
    {
        if ($taxClass->getClassId()) {
            throw new InputException(self::CLASS_ID_NOT_ALLOWED);
        }

        $this->validateTaxClassData($taxClass);
        $taxModel = $this->converter->createTaxClassModel($taxClass);
        try {
            $taxModel->save();
        } catch (ModelException $e) {
            if (strpos($e->getMessage(), \Magento\Tax\Model\Resource\TaxClass::UNIQUE_TAX_CLASS_MSG) !== false) {
                throw new InputException(
                    'A class with the same name already exists for ClassType %classType.',
                    ['classType' => $taxClass->getClassType()]
                );
            } else {
                throw $e;
            }
        }
        return $taxModel->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxClass($taxClassId)
    {
        $taxClassModel = $this->taxClassModelFactory->create()->load($taxClassId);
        if (!$taxClassModel->getId()) {
            throw NoSuchEntityException::singleField(TaxClassDataObject::KEY_ID, $taxClassId);
        }
        return $this->converter->createTaxClassData($taxClassModel);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTaxClass($taxClassId, TaxClassDataObject $taxClass)
    {
        if ($taxClass->getClassId()) {
            throw new InputException(self::CLASS_ID_NOT_ALLOWED);
        }

        $this->validateTaxClassData($taxClass);

        if (!$taxClassId) {
            throw InputException::invalidFieldValue('taxClassId', $taxClassId);
        }

        $originalTaxClassModel = $this->taxClassModelFactory->create()->load($taxClassId);
        if (!$originalTaxClassModel->getId()) {
            throw NoSuchEntityException::singleField('taxClassId', $taxClassId);
        }

        $taxClassModel = $this->converter->createTaxClassModel($taxClass);
        $taxClassModel->setId($taxClassId);

        /* should not be allowed to switch the tax class type */
        if ($originalTaxClassModel->getClassType() !== $taxClassModel->getClassType()) {
            throw new InputException('Updating classType is not allowed.');
        }

        try {
            $taxClassModel->save();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTaxClass($taxClassId)
    {
        $taxClassModel = $this->taxClassModelFactory->create()->load($taxClassId);
        if (!$taxClassModel->getId()) {
            throw NoSuchEntityException::singleField('taxClassId', $taxClassId);
        }

        try {
            $taxClassModel->delete();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Validate TaxClass Data
     *
     * @param TaxClassDataObject $taxClass
     * @return void
     * @throws InputException
     */
    protected function validateTaxClassData(TaxClassDataObject $taxClass)
    {
        $exception = new InputException();

        if (!\Zend_Validate::is(trim($taxClass->getClassName()), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => TaxClassDataObject::KEY_NAME]);
        }

        $classType = $taxClass->getClassType();
        if (!\Zend_Validate::is(trim($classType), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => TaxClassDataObject::KEY_TYPE]);
        } else if ($classType !== TaxClassDataObject::TYPE_CUSTOMER
            && $classType !== TaxClassDataObject::TYPE_PRODUCT
        ) {
            $exception->addError(
                InputException::INVALID_FIELD_VALUE,
                ['fieldName' => TaxClassDataObject::KEY_TYPE, 'value' => $classType]
            );
        }

        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }

    /**
     * Retrieve tax classes which match a specific criteria.
     *
     * @param \Magento\Framework\Service\V1\Data\SearchCriteria $searchCriteria
     * @return \Magento\Tax\Service\V1\Data\SearchResults containing Data\TaxClass
     * @throws \Magento\Framework\Exception\InputException
     */
    public function searchTaxClass(\Magento\Framework\Service\V1\Data\SearchCriteria $searchCriteria)
    {
        $this->searchResultsBuilder->setSearchCriteria($searchCriteria);
        /** @var TaxClassCollection $collection */
        $collection = $this->taxClassCollectionFactory->create();
        /** TODO: This method duplicates functionality of search methods in other services and should be refactored. */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $this->searchResultsBuilder->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $field => $direction) {
                $collection->addOrder($field, $direction == SearchCriteria::SORT_ASC ? 'ASC' : 'DESC');
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $taxClasses = [];
        /** @var \Magento\Tax\Model\ClassModel $taxClassModel */
        foreach ($collection->getItems() as $taxClassModel) {
            $taxClasses[] = $this->converter->createTaxClassData($taxClassModel);
        }
        $this->searchResultsBuilder->setItems($taxClasses);
        return $this->searchResultsBuilder->create();
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * TODO: This method duplicates functionality of search methods in other services and should be refactored.
     *
     * @param FilterGroup $filterGroup
     * @param TaxClassCollection $collection
     * @return void
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
