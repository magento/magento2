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

use Magento\Tax\Model\Calculation\Rate\Converter;
use Magento\Tax\Service\V1\Data\TaxRate as TaxRateDataObject;
use Magento\Tax\Model\Calculation\Rate as RateModel;
use Magento\Tax\Service\V1\Data\TaxRateBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Tax\Model\Calculation\RateRegistry;

/**
 * Handles tax rate CRUD operations
 *
 */
class TaxRateService implements TaxRateServiceInterface
{
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
     * Constructor
     *
     * @param TaxRateBuilder $rateBuilder
     * @param Converter $converter
     * @param RateRegistry $rateRegistry
     */
    public function __construct(
        TaxRateBuilder $rateBuilder,
        Converter $converter,
        RateRegistry $rateRegistry
    ) {
        $this->rateBuilder = $rateBuilder;
        $this->converter = $converter;
        $this->rateRegistry = $rateRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function createTaxRate(TaxRateDataObject $taxRate)
    {
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
     * Save Tax Rate
     *
     * @param TaxRateDataObject $taxRate
     * @throws InputException
     * @throws \Magento\Framework\Model\Exception
     * @return RateModel
     */
    protected function saveTaxRate(TaxRateDataObject $taxRate)
    {
        $this->validate($taxRate);
        $taxRateModel = $this->converter->createTaxRateModel($taxRate);
        $taxRateModel->save();
        $this->rateRegistry->registerTaxRate($taxRateModel);
        return $taxRateModel;
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
        if (!\Zend_Validate::is(trim($taxRate->getCountryId()), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'country_id']);
        }
        if (!\Zend_Validate::is(trim($taxRate->getRegionId()), 'NotEmpty')) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'region_id']);
        }
        if (!\Zend_Validate::is(trim($taxRate->getPercentageRate()), 'NotEmpty')) {
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
