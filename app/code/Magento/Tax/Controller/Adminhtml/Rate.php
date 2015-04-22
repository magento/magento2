<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Controller\Adminhtml;

/**
 * Adminhtml tax rate controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Rate extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Tax\Api\TaxRateRepositoryInterface
     */
    protected $_taxRateRepository;

    /**
     * @var \Magento\Tax\Api\Data\TaxRateInterfaceFactory
     */
    protected $_taxRateDataObjectFactory;

    /**
     * @var \Magento\Tax\Api\Data\TaxRateTitleInterfaceFactory
     */
    protected $_taxRateTitleDataObjectFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Tax\Api\TaxRateRepositoryInterface $taxRateRepository
     * @param \Magento\Tax\Api\Data\TaxRateInterfaceFactory $taxRateDataObjectFactory
     * @param \Magento\Tax\Api\Data\TaxRateTitleInterfaceFactory $taxRateTitleDataObjectFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Tax\Api\TaxRateRepositoryInterface $taxRateRepository,
        \Magento\Tax\Api\Data\TaxRateInterfaceFactory $taxRateDataObjectFactory,
        \Magento\Tax\Api\Data\TaxRateTitleInterfaceFactory $taxRateTitleDataObjectFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_taxRateRepository = $taxRateRepository;
        $this->_taxRateDataObjectFactory = $taxRateDataObjectFactory;
        $this->_taxRateTitleDataObjectFactory = $taxRateTitleDataObjectFactory;
        parent::__construct($context);
    }

    /**
     * Validate/Filter Rate Data
     *
     * @param array $rateData
     * @return array
     */
    protected function _processRateData($rateData)
    {
        $result = [];
        foreach ($rateData as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->_processRateData($value);
            } else {
                $result[$key] = trim(strip_tags($value));
            }
        }
        return $result;
    }

    /**
     * Initialize action
     *
     * @return \Magento\Backend\App\Action
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_Tax::sales_tax_rates'
        )->_addBreadcrumb(
            __('Sales'),
            __('Sales')
        )->_addBreadcrumb(
            __('Tax'),
            __('Tax')
        );
        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Tax::manage_tax');
    }

    /**
     * Populate a tax rate data object
     *
     * @param array $formData
     * @return \Magento\Tax\Api\Data\TaxRateInterface
     */
    protected function populateTaxRateData($formData)
    {
        $taxRate = $this->_taxRateDataObjectFactory->create();
        $taxRate->setId($this->extractFormData($formData, 'tax_calculation_rate_id'))
            ->setTaxCountryId($this->extractFormData($formData, 'tax_country_id'))
            ->setTaxRegionId($this->extractFormData($formData, 'tax_region_id'))
            ->setTaxPostcode($this->extractFormData($formData, 'tax_postcode'))
            ->setCode($this->extractFormData($formData, 'code'))
            ->setRate($this->extractFormData($formData, 'rate'));
        if (isset($formData['zip_is_range']) && $formData['zip_is_range']) {
            $taxRate->setZipFrom($this->extractFormData($formData, 'zip_from'))
                ->setZipTo($this->extractFormData($formData, 'zip_to'))->setZipIsRange(1);
        }

        if (isset($formData['title'])) {
            $titles = [];
            foreach ($formData['title'] as $storeId => $value) {
                $titles[] = $this->_taxRateTitleDataObjectFactory->create()->setStoreId($storeId)->setValue($value);
            }
            $taxRate->setTitles($titles);
        }

        return $taxRate;
    }


    /**
     * Extract tax rate data in a format which is array
     *
     * @param \Magento\Tax\Api\Data\TaxRateInterface $taxRate
     * @return array
     */
    protected function extractTaxRateData($taxRate)
    {
        $formData = [
            'tax_calculation_rate_id' => $taxRate->getId(),
            'tax_country_id' => $taxRate->getTaxCountryId(),
            'tax_region_id' => $taxRate->getTaxRegionId(),
            'tax_postcode' => $taxRate->getTaxPostcode(),
            'code' => $taxRate->getCode(),
            'rate' => $taxRate->getRate(),
            'zip_is_range' => false,
        ];

        if ($taxRate->getZipFrom() && $taxRate->getZipTo()) {
            $formData['zip_is_range'] = true;
            $formData['zip_from'] = $taxRate->getZipFrom();
            $formData['zip_to'] = $taxRate->getZipTo();
        }

        if ($taxRate->getTitles()) {
            $titleData = [];
            foreach ($taxRate->getTitles() as $title) {
                $titleData[] = [$title->getStoreId() => $title->getValue()];
            }
            $formData['title'] = $titleData;
        }

        return $formData;
    }

    /**
     * Determines if an array value is set in the form data array and returns it.
     *
     * @param array $formData the form to get data from
     * @param string $fieldName the key
     * @return null|string
     */
    protected function extractFormData($formData, $fieldName)
    {
        if (isset($formData[$fieldName])) {
            return $formData[$fieldName];
        }
        return null;
    }
}
