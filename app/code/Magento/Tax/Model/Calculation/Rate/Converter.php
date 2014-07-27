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

use Magento\Directory\Model\Region;
use Magento\Tax\Model\Calculation\Rate as TaxRateModel;
use Magento\Tax\Model\Calculation\RateFactory as TaxRateModelFactory;
use Magento\Tax\Service\V1\Data\TaxRate as TaxRateDataObject;
use Magento\Tax\Service\V1\Data\TaxRateBuilder as TaxRateDataObjectBuilder;
use Magento\Tax\Service\V1\Data\TaxRateTitleBuilder as TaxRateTitleDataObjectBuilder;
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
     * @var TaxRateTitleDataObjectBuilder
     */
    protected $taxRateTitleDataObjectBuilder;

    /**
     * @var Region
     */
    protected $directoryRegion;

    /**
     * @param TaxRateDataObjectBuilder $taxRateDataObjectBuilder
     * @param TaxRateModelFactory $taxRateModelFactory
     * @param ZipRangeDataObjectBuilder $zipRangeDataObjectBuilder
     * @param TaxRateTitleDataObjectBuilder $taxRateTitleDataObjectBuilder
     * @param Region $directoryRegion
     */
    public function __construct(
        TaxRateDataObjectBuilder $taxRateDataObjectBuilder,
        TaxRateModelFactory $taxRateModelFactory,
        ZipRangeDataObjectBuilder $zipRangeDataObjectBuilder,
        TaxRateTitleDataObjectBuilder $taxRateTitleDataObjectBuilder,
        Region $directoryRegion
    ) {
        $this->taxRateDataObjectBuilder = $taxRateDataObjectBuilder;
        $this->taxRateModelFactory = $taxRateModelFactory;
        $this->zipRangeDataObjectBuilder = $zipRangeDataObjectBuilder;
        $this->taxRateTitleDataObjectBuilder = $taxRateTitleDataObjectBuilder;
        $this->directoryRegion = $directoryRegion;
    }

    /**
     * Convert a rate model to a TaxRate data object
     *
     * @param TaxRateModel $rateModel
     * @return TaxRateDataObject
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
        /* tax region id may be 0 which is "*" which would fail an if check */
        if ($rateModel->getTaxRegionId() !== null) {
            $this->taxRateDataObjectBuilder->setRegionId($rateModel->getTaxRegionId());
            $regionName = $this->directoryRegion->load($rateModel->getTaxRegionId())->getCode();
            $this->taxRateDataObjectBuilder->setRegionName($regionName);
        }
        if ($rateModel->getRegionName()) {
            $this->taxRateDataObjectBuilder->setRegionName($rateModel->getRegionName());
        }
        if ($rateModel->getTaxPostcode()) {
            $this->taxRateDataObjectBuilder->setPostcode($rateModel->getTaxPostcode());
        }
        if ($rateModel->getCode()) {
            $this->taxRateDataObjectBuilder->setCode($rateModel->getCode());
        }
        if ($rateModel->getRate()) {
            $this->taxRateDataObjectBuilder->setPercentageRate((float)$rateModel->getRate());
        }
        if ($rateModel->getZipIsRange()) {
            $zipRange = $this->zipRangeDataObjectBuilder->populateWithArray([])
                ->setFrom($rateModel->getZipFrom())
                ->setTo($rateModel->getZipTo())
                ->create();
            $this->taxRateDataObjectBuilder->setZipRange($zipRange);
        }
        $this->taxRateDataObjectBuilder->setTitles($this->createTitleArrayFromModel($rateModel));
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
        $rateModel->setRegionName($taxRate->getRegionName());
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

    /**
     * Convert a tax rate data object to an array of associated titles
     *
     * @param TaxRateDataObject $taxRate
     * @return array
     */
    public function createTitleArrayFromServiceObject(TaxRateDataObject $taxRate)
    {
        $titles = $taxRate->getTitles();
        $titleData = [];
        if ($titles) {
            foreach ($titles as $title) {
                $titleData[$title->getStoreId()] = $title->getValue();
            }
        }
        return $titleData;
    }

    /**
     * Create an array with tax rate titles having tax rate model.
     *
     * @param TaxRateModel $rateModel
     * @return array
     */
    public function createTitleArrayFromModel(TaxRateModel $rateModel)
    {
        $titlesData = $rateModel->getTitles();
        $titles = [];
        if ($titlesData) {
            foreach ($titlesData as $title) {
                $titles[] = $this->taxRateTitleDataObjectBuilder
                    ->setStoreId($title->getStoreId())
                    ->setValue($title->getValue())
                    ->create();
            }
        }
        return $titles;
    }
}
