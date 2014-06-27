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
namespace Magento\Tax\Model\Calculation\Rate;

use Magento\Tax\Model\Calculation\Rate as TaxRateModel;
use Magento\Tax\Model\Calculation\RateFactory as TaxRateModelFactory;
use Magento\Tax\Service\V1\Data\TaxRate as TaxRateDataObject;
use Magento\Tax\Service\V1\Data\TaxRateBuilder as TaxRateDataObjectBuilder;
use Magento\Tax\Service\V1\Data\ZipRangeBuilder as ZipRangeDataObjectBuilder;

/**
 * Tax Rate Model converter.
 *
 * Converts a Tax Rate Model to a Data Object or vice versa.
 */
class Converter
{
    /**
     * @var TaxRateDataObjectBuilder
     */
    protected $taxRateDataObjectBuilder;

    /**
     * @var TaxRateModelFactory
     */
    protected $taxRateModelFactory;

    /**
     * @var ZipRangeDataObjectBuilder
     */
    protected $zipRangeDataObjectBuilder;

    /**
     * @param TaxRateDataObjectBuilder $taxRateDataObjectBuilder
     * @param TaxRateModelFactory $taxRateModelFactory
     * @param ZipRangeDataObjectBuilder $zipRangeDataObjectBuilder
     */
    public function __construct(
        TaxRateDataObjectBuilder $taxRateDataObjectBuilder,
        TaxRateModelFactory $taxRateModelFactory,
        ZipRangeDataObjectBuilder $zipRangeDataObjectBuilder
    ) {
        $this->taxRateDataObjectBuilder = $taxRateDataObjectBuilder;
        $this->taxRateModelFactory = $taxRateModelFactory;
        $this->zipRangeDataObjectBuilder = $zipRangeDataObjectBuilder;
    }

    /**
     * Convert a rate model to a TaxRate data object
     *
     * @param TaxRateModel $rateModel
     * @return TaxRateDataObject
     */
    public function createTaxRateDataObjectFromModel(TaxRateModel $rateModel)
    {
        $this->taxRateDataObjectBuilder->populateWithArray([]);
        if ($rateModel->getId()) {
            $this->taxRateDataObjectBuilder->setId($rateModel->getId());
        }
        if ($rateModel->getTaxCountryId()) {
            $this->taxRateDataObjectBuilder->setCountryId($rateModel->getTaxCountryId());
        }
        if ($rateModel->getTaxRegionId()) {
            $this->taxRateDataObjectBuilder->setRegionId($rateModel->getTaxRegionId());
        }
        if ($rateModel->getTaxPostcode()) {
            $this->taxRateDataObjectBuilder->setPostcode($rateModel->getTaxPostcode());
        }
        if ($rateModel->getCode()) {
            $this->taxRateDataObjectBuilder->setCode($rateModel->getCode());
        }
        if ($rateModel->getRate()) {
            $this->taxRateDataObjectBuilder->setPercentageRate($rateModel->getRate());
        }
        if ($rateModel->getZipIsRange()) {
            $zipRange = $this->zipRangeDataObjectBuilder->populateWithArray([])
                ->setFrom($rateModel->getZipFrom())
                ->setTo($rateModel->getZipTo())
                ->create();
            $this->taxRateDataObjectBuilder->setZipRange($zipRange);
        }
        return $this->taxRateDataObjectBuilder->create();
    }

    /**
     * Convert a TaxRate data object to rate model
     *
     * @param TaxRateDataObject $taxRate
     * @return TaxRateModel
     */
    public function createTaxRateModel(TaxRateDataObject $taxRate)
    {
        $rateModel = $this->taxRateModelFactory->create();
        $rateId = $taxRate->getId();
        if ($rateId) {
            $rateModel->setId($rateId);
        }
        $rateModel->setTaxCountryId($taxRate->getCountryId());
        $rateModel->setTaxRegionId($taxRate->getRegionId());
        $rateModel->setRate($taxRate->getPercentageRate());
        $rateModel->setCode($taxRate->getCode());
        $rateModel->setTaxPostcode($taxRate->getPostCode());
        $zipRange = $taxRate->getZipRange();
        if ($zipRange) {
            $zipFrom = $zipRange->getFrom();
            $zipTo = $zipRange->getTo();
            if (!empty($zipFrom) || !empty($zipTo)) {
                $rateModel->setZipIsRange(1);
            }
            $rateModel->setZipFrom($zipFrom);
            $rateModel->setZipTo($zipTo);
        }
        return $rateModel;
    }
}
