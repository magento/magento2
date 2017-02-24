<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation;

use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Model\Calculation\Rate\Converter;
use Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RateRepository implements \Magento\Tax\Api\TaxRateRepositoryInterface
{
    const MESSAGE_TAX_RATE_ID_IS_NOT_ALLOWED = 'id is not expected for this request.';

    /**
     * Tax rate model and tax rate data object converter
     *
     * @var  Converter
     */
    protected $converter;

    /**
     * Tax rate registry
     *
     * @var  RateRegistry
     */
    protected $rateRegistry;

    /**
     * @var \Magento\Tax\Api\Data\TaxRuleSearchResultsInterfaceFactory
     */
    private $taxRateSearchResultsFactory;

    /**
     * @var RateFactory
     */
    private $rateFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * @var \Magento\Tax\Model\ResourceModel\Calculation\Rate
     */
    protected $resourceModel;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $joinProcessor;

    /** @var  CollectionProcessorInterface */
    private $collectionProcessor;

    /**
     * @param Converter $converter
     * @param RateRegistry $rateRegistry
     * @param \Magento\Tax\Api\Data\TaxRuleSearchResultsInterfaceFactory $taxRateSearchResultsFactory
     * @param RateFactory $rateFactory
     * @param CountryFactory $countryFactory
     * @param RegionFactory $regionFactory
     * @param \Magento\Tax\Model\ResourceModel\Calculation\Rate $rateResource
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        Converter $converter,
        RateRegistry $rateRegistry,
        \Magento\Tax\Api\Data\TaxRuleSearchResultsInterfaceFactory $taxRateSearchResultsFactory,
        RateFactory $rateFactory,
        CountryFactory $countryFactory,
        RegionFactory $regionFactory,
        \Magento\Tax\Model\ResourceModel\Calculation\Rate $rateResource,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->converter = $converter;
        $this->rateRegistry = $rateRegistry;
        $this->taxRateSearchResultsFactory = $taxRateSearchResultsFactory;
        $this->rateFactory = $rateFactory;
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
        $this->resourceModel = $rateResource;
        $this->joinProcessor = $joinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Tax\Api\Data\TaxRateInterface $taxRate)
    {
        if ($taxRate->getId()) {
            $this->rateRegistry->retrieveTaxRate($taxRate->getId());
        }
        $this->validate($taxRate);
        $taxRateTitles = $this->converter->createTitleArrayFromServiceObject($taxRate);
        try {
            $this->resourceModel->save($taxRate);
            $taxRate->saveTitles($taxRateTitles);
        } catch (LocalizedException $e) {
            throw $e;
        }
        $this->rateRegistry->registerTaxRate($taxRate);
        return $taxRate;
    }

    /**
     * {@inheritdoc}
     */
    public function get($rateId)
    {
        return $this->rateRegistry->retrieveTaxRate($rateId);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Tax\Api\Data\TaxRateInterface $taxRate)
    {
        return $this->resourceModel->delete($taxRate);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($rateId)
    {
        $rateModel = $this->rateRegistry->retrieveTaxRate($rateId);
        $this->delete($rateModel);
        $this->rateRegistry->removeTaxRate($rateId);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection $collection */
        $collection = $this->rateFactory->create()->getCollection();
        $this->joinProcessor->process($collection);
        $collection->joinRegionTable();

        $this->collectionProcessor->process($searchCriteria, $collection);
        $taxRate = [];

        /** @var \Magento\Tax\Model\Calculation\Rate $taxRateModel */
        foreach ($collection as $taxRateModel) {
            $taxRate[] = $taxRateModel;
        }

        return $this->taxRateSearchResultsFactory->create()
            ->setItems($taxRate)
            ->setTotalCount($collection->getSize())
            ->setSearchCriteria($searchCriteria);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @deprecated 
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
     * @deprecated 
     * @param string $field a field name that should be translated to a DB column name.
     * @return string
     */
    protected function translateField($field)
    {
        switch ($field) {
            case Rate::KEY_REGION_NAME:
                return 'region_table.code';
            default:
                return "main_table." . $field;
        }
    }

    /**
     * Validate tax rate
     *
     * @param \Magento\Tax\Api\Data\TaxRateInterface $taxRate
     * @throws InputException
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function validate(\Magento\Tax\Api\Data\TaxRateInterface $taxRate)
    {
        $exception = new InputException();

        $countryCode = $taxRate->getTaxCountryId();
        if (!\Zend_Validate::is($countryCode, 'NotEmpty')) {
            $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'country_id']));
        } elseif (!\Zend_Validate::is(
            $this->countryFactory->create()->loadByCode($countryCode)->getId(),
            'NotEmpty'
        )) {
            $exception->addError(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    [
                        'fieldName' => 'country_id',
                        'value' => $countryCode
                    ]
                )
            );
        }

        $regionCode = $taxRate->getTaxRegionId();
        // if regionCode eq 0 (all regions *), do not validate with existing region list
        if (\Zend_Validate::is($regionCode, 'NotEmpty') &&
            $regionCode != "0" && !\Zend_Validate::is(
                $this->regionFactory->create()->load($regionCode)->getId(),
                'NotEmpty'
            )
        ) {
            $exception->addError(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['fieldName' => 'region_id', 'value' => $regionCode]
                )
            );
        }

        if (!\Zend_Validate::is($taxRate->getRate(), 'NotEmpty')) {
            $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'percentage_rate']));
        }

        if (!\Zend_Validate::is(trim($taxRate->getCode()), 'NotEmpty')) {
            $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'code']));
        }

        if ($taxRate->getZipIsRange()) {
            $zipRangeFromTo = [
                'zip_from' => $taxRate->getZipFrom(),
                'zip_to' => $taxRate->getZipTo(),
            ];
            foreach ($zipRangeFromTo as $key => $value) {
                if (!is_numeric($value) || $value < 0) {
                    $exception->addError(
                        __(
                            'Invalid value of "%value" provided for the %fieldName field.',
                            ['fieldName' => $key, 'value' => $value]
                        )
                    );
                }
            }
            if ($zipRangeFromTo['zip_from'] > $zipRangeFromTo['zip_to']) {
                $exception->addError(__('Range To should be equal or greater than Range From.'));
            }
        } else {
            if (!\Zend_Validate::is(trim($taxRate->getTaxPostcode()), 'NotEmpty')) {
                $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'postcode']));
            }
        }

        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Tax\Model\Api\SearchCriteria\TaxRateCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}
