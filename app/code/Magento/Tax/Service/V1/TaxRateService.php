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

use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Model\Exception as ModelException;
use Magento\Framework\Service\V1\Data\Search\FilterGroup;
use Magento\Framework\Service\V1\Data\SearchCriteria;
use Magento\Tax\Model\Calculation\Rate as RateModel;
use Magento\Tax\Model\Calculation\Rate\Converter;
use Magento\Tax\Model\Calculation\RateFactory;
use Magento\Tax\Model\Calculation\RateRegistry;
use Magento\Tax\Model\Resource\Calculation\Rate\Collection;
use Magento\Tax\Service\V1\Data\TaxRate as TaxRateDataObject;
use Magento\Tax\Service\V1\Data\TaxRateBuilder;
use Magento\Framework\Service\V1\Data\SortOrder;

/**
 * Handles tax rate CRUD operations
 *
 */
class TaxRateService implements TaxRateServiceInterface
{
    const MESSAGE_TAX_RATE_ID_IS_NOT_ALLOWED = 'id is not expected for this request.';

    /**
     * Tax rate model and tax rate data object converter
     *
     * @var  Converter
     */
    protected $converter;

    /**
     * Tax rate data object builder
     *
     * @var  TaxRateBuilder
     */
    protected $rateBuilder;

    /**
     * Tax rate registry
     *
     * @var  RateRegistry
     */
    protected $rateRegistry;

    /**
     * @var Data\TaxRateSearchResultsBuilder
     */
    private $taxRateSearchResultsBuilder;

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
     * Constructor
     *
     * @param TaxRateBuilder $rateBuilder
     * @param Converter $converter
     * @param RateRegistry $rateRegistry
     * @param Data\TaxRateSearchResultsBuilder $taxRateSearchResultsBuilder
     * @param RateFactory $rateFactory
     * @param CountryFactory $countryFactory
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        TaxRateBuilder $rateBuilder,
        Converter $converter,
        RateRegistry $rateRegistry,
        Data\TaxRateSearchResultsBuilder $taxRateSearchResultsBuilder,
        RateFactory $rateFactory,
        CountryFactory $countryFactory,
        RegionFactory $regionFactory
    ) {
        $this->rateBuilder = $rateBuilder;
        $this->converter = $converter;
        $this->rateRegistry = $rateRegistry;
        $this->taxRateSearchResultsBuilder = $taxRateSearchResultsBuilder;
        $this->rateFactory = $rateFactory;
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createTaxRate(TaxRateDataObject $taxRate)
    {
        if ($taxRate->getId()) {
            throw new InputException(self::MESSAGE_TAX_RATE_ID_IS_NOT_ALLOWED);
        }
        $rateModel = $this->saveTaxRate($taxRate);
        return $this->converter->createTaxRateDataObjectFromModel($rateModel);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxRate($rateId)
    {
        $rateModel = $this->rateRegistry->retrieveTaxRate($rateId);
        return $this->converter->createTaxRateDataObjectFromModel($rateModel);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTaxRate(TaxRateDataObject $taxRate)
    {
        // Only update existing tax rates
        $this->rateRegistry->retrieveTaxRate($taxRate->getId());

        $this->saveTaxRate($taxRate);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTaxRate($rateId)
    {
        $rateModel = $this->rateRegistry->retrieveTaxRate($rateId);
        $rateModel->delete();
        $this->rateRegistry->removeTaxRate($rateId);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function searchTaxRates(SearchCriteria $searchCriteria)
    {
        /** @var \Magento\Tax\Model\Resource\Calculation\Rate\Collection $collection */
        $collection = $this->rateFactory->create()->getCollection();
        $collection->joinRegionTable();

        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }

        $sortOrders = $searchCriteria->getSortOrders();
        /** @var SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $this->translateField($sortOrder->getField()),
                    ($sortOrder->getDirection() == SearchCriteria::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $taxRate = [];

        /** @var \Magento\Tax\Model\Calculation\Rate $taxRateModel */
        foreach ($collection as $taxRateModel) {
            $taxRate[] = $this->converter->createTaxRateDataObjectFromModel($taxRateModel);
        }

        return $this->taxRateSearchResultsBuilder
            ->setItems($taxRate)
            ->setTotalCount($collection->getSize())
            ->setSearchCriteria($searchCriteria)
            ->create();
    }

    /**
     * Save Tax Rate
     *
     * @param TaxRateDataObject $taxRate
     * @throws InputException
     * @throws ModelException
     * @return RateModel
     */
    protected function saveTaxRate(TaxRateDataObject $taxRate)
    {
        $this->validate($taxRate);
        $taxRateModel = $this->converter->createTaxRateModel($taxRate);
        $taxRateTitles = $this->converter->createTitleArrayFromServiceObject($taxRate);
        try {
            $taxRateModel->save();
            $taxRateModel->saveTitles($taxRateTitles);
        } catch (ModelException $e) {
            if ($e->getCode() == ModelException::ERROR_CODE_ENTITY_ALREADY_EXISTS) {
                throw new InputException($e->getMessage());
            } else {
                throw $e;
            }
        }
        $this->rateRegistry->registerTaxRate($taxRateModel);
        return $taxRateModel;
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
            case TaxRateDataObject::KEY_POSTCODE:
            case TaxRateDataObject::KEY_COUNTRY_ID:
            case TaxRateDataObject::KEY_REGION_ID:
                return 'tax_' . $field;
            case TaxRateDataObject::KEY_PERCENTAGE_RATE:
                return 'rate';
            case TaxRateDataObject::KEY_REGION_NAME:
                return 'region_table.code';
            default:
                return "main_table." . $field;
        }
    }

    /**
     * Validate tax rate
     *
     * @param TaxRateDataObject $taxRate
     * @throws InputException
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function validate(TaxRateDataObject $taxRate)
    {
        $exception = new InputException();

        $countryCode = $taxRate->getCountryId();
        if (!\Zend_Validate::is($countryCode, 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'country_id']);
        } else if (!\Zend_Validate::is($this->countryFactory->create()->loadByCode($countryCode)->getId(), 'NotEmpty')) {
            $exception->addError(InputException::INVALID_FIELD_VALUE, ['fieldName' => 'country_id', 'value' => $countryCode]);
        }

        $regionCode = $taxRate->getRegionId();
        if (\Zend_Validate::is($regionCode, 'NotEmpty') &&
            !\Zend_Validate::is($this->regionFactory->create()->load($regionCode)->getId(), 'NotEmpty')) {
            $exception->addError(InputException::INVALID_FIELD_VALUE, ['fieldName' => 'region_id', 'value' => $regionCode]);
        }

        if (!\Zend_Validate::is($taxRate->getPercentageRate(), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'percentage_rate']);
        }

        if (!\Zend_Validate::is(trim($taxRate->getCode()), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'code']);
        }

        if ($taxRate->getZipRange()) {
            $zipRangeFromTo = [
                'zip_from' => $taxRate->getZipRange()->getFrom(),
                'zip_to' => $taxRate->getZipRange()->getTo()
            ];
            foreach ($zipRangeFromTo as $key => $value) {
                if (!is_numeric($value) || $value < 0) {
                    $exception->addError(
                        InputException::INVALID_FIELD_VALUE,
                        ['fieldName' => $key, 'value' => $value]
                    );
                }
            }
            if ($zipRangeFromTo['zip_from'] > $zipRangeFromTo['zip_to']) {
                $exception->addError('Range To should be equal or greater than Range From.');
            }
        } else {
            if (!\Zend_Validate::is(trim($taxRate->getPostcode()), 'NotEmpty')) {
                $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'postcode']);
            }
        }

        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }
}
