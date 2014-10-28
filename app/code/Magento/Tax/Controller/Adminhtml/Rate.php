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
     * @var \Magento\Tax\Service\V1\TaxRateServiceInterface
     */
    protected $_taxRateService;

    /**
     * @var \Magento\Tax\Service\V1\Data\TaxRateBuilder
     */
    protected $_taxRateBuilder;

    /**
     * @var \Magento\Tax\Service\V1\Data\ZipRangeBuilder
     */
    protected $_zipRangeBuilder;

    /**
     * @var \Magento\Tax\Service\V1\Data\TaxRateTitleBuilder
     */
    protected $_taxRateTitleBuilder;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Tax\Service\V1\TaxRateServiceInterface $taxRateService
     * @param \Magento\Tax\Service\V1\Data\TaxRateBuilder $taxRateBuilder
     * @param \Magento\Tax\Service\V1\Data\ZipRangeBuilder $zipRangeBuilder
     * @param \Magento\Tax\Service\V1\Data\TaxRateTitleBuilder $taxRateTitleBuilder
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Tax\Service\V1\TaxRateServiceInterface $taxRateService,
        \Magento\Tax\Service\V1\Data\TaxRateBuilder $taxRateBuilder,
        \Magento\Tax\Service\V1\Data\ZipRangeBuilder $zipRangeBuilder,
        \Magento\Tax\Service\V1\Data\TaxRateTitleBuilder $taxRateTitleBuilder
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_taxRateService = $taxRateService;
        $this->_taxRateBuilder = $taxRateBuilder;
        $this->_zipRangeBuilder = $zipRangeBuilder;
        $this->_taxRateTitleBuilder = $taxRateTitleBuilder;
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
        $result = array();
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
     * @return \Magento\Tax\Service\V1\Data\TaxRate
     */
    protected function populateTaxRateData($formData)
    {
        $this->_taxRateBuilder->setId($this->extractFormData($formData, 'tax_calculation_rate_id'))
            ->setCountryId($this->extractFormData($formData, 'tax_country_id'))
            ->setRegionId($this->extractFormData($formData, 'tax_region_id'))
            ->setPostcode($this->extractFormData($formData, 'tax_postcode'))
            ->setCode($this->extractFormData($formData, 'code'))
            ->setPercentageRate($this->extractFormData($formData, 'rate'));

        if (isset($formData['zip_is_range']) && $formData['zip_is_range']) {
            $this->_zipRangeBuilder->setFrom($this->extractFormData($formData, 'zip_from'))
                ->setTo($this->extractFormData($formData, 'zip_to'));
            $zipRange = $this->_zipRangeBuilder->create();
            $this->_taxRateBuilder->setZipRange($zipRange);
        }

        if (isset($formData['title'])) {
            $titles = [];
            foreach ($formData['title'] as $storeId => $value) {
                $titles[] = $this->_taxRateTitleBuilder->setStoreId($storeId)->setValue($value)->create();
            }
            $this->_taxRateBuilder->setTitles($titles);
        }

        return $this->_taxRateBuilder->create();
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
