<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Admin product tax class add form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Block\Adminhtml\Rate;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Controller\RegistryConstants;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    const FORM_ELEMENT_ID = 'rate-form';

    /**
     * @var null
     */
    protected $_titles = null;

    /**
     * @var string
     */
    protected $_template = 'rate/form.phtml';

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data|null
     */
    protected $_taxData = null;

    /**
     * @var \Magento\Tax\Block\Adminhtml\Rate\Title\FieldsetFactory
     */
    protected $_fieldsetFactory;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $_country;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Tax\Api\TaxRateRepositoryInterface
     */
    protected $_taxRateRepository;

    /**
     * @var \Magento\Tax\Model\TaxRateCollection
     */
    protected $_taxRateCollection;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\Config\Source\Country $country
     * @param \Magento\Tax\Block\Adminhtml\Rate\Title\FieldsetFactory $fieldsetFactory
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Tax\Api\TaxRateRepositoryInterface $taxRateRepository
     * @param \Magento\Tax\Model\TaxRateCollection $taxRateCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\Config\Source\Country $country,
        \Magento\Tax\Block\Adminhtml\Rate\Title\FieldsetFactory $fieldsetFactory,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Tax\Api\TaxRateRepositoryInterface $taxRateRepository,
        \Magento\Tax\Model\TaxRateCollection $taxRateCollection,
        array $data = []
    ) {
        $this->_regionFactory = $regionFactory;
        $this->_country = $country;
        $this->_fieldsetFactory = $fieldsetFactory;
        $this->_taxData = $taxData;
        $this->_taxRateRepository = $taxRateRepository;
        $this->_taxRateCollection = $taxRateCollection;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDestElementId(self::FORM_ELEMENT_ID);
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $taxRateId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_TAX_RATE_ID);

        try {
            if ($taxRateId) {
                $taxRateDataObject = $this->_taxRateRepository->get($taxRateId);
            }
        } catch (NoSuchEntityException $e) {
            /* tax rate not found */
        }

        $sessionFormValues = (array)$this->_coreRegistry->registry(RegistryConstants::CURRENT_TAX_RATE_FORM_DATA);
        $formData = isset($taxRateDataObject) ? $this->extractTaxRateData($taxRateDataObject) : [];
        $formData = array_merge($formData, $sessionFormValues);

        if (isset($formData['zip_is_range']) && $formData['zip_is_range'] && !isset($formData['tax_postcode'])) {
            $formData['tax_postcode'] = $formData['zip_from'] . '-' . $formData['zip_to'];
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $countries = $this->_country->toOptionArray(false, 'US');
        unset($countries[0]);

        if (!isset($formData['tax_country_id'])) {
            $formData['tax_country_id'] = $this->_scopeConfig->getValue(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        if (!isset($formData['tax_region_id'])) {
            $formData['tax_region_id'] = $this->_scopeConfig->getValue(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_REGION,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        $regionCollection = $this->_regionFactory->create()->getCollection()->addCountryFilter(
            $formData['tax_country_id']
        );

        $regions = $regionCollection->toOptionArray();
        if ($regions) {
            $regions[0]['label'] = '*';
        } else {
            $regions = [['value' => '', 'label' => '*']];
        }

        $legend = $this->getShowLegend() ? __('Tax Rate Information') : '';
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => $legend]);

        if (isset($formData['tax_calculation_rate_id']) && $formData['tax_calculation_rate_id'] > 0) {
            $fieldset->addField(
                'tax_calculation_rate_id',
                'hidden',
                ['name' => 'tax_calculation_rate_id', 'value' => $formData['tax_calculation_rate_id']]
            );
        }

        $fieldset->addField(
            'code',
            'text',
            [
                'name' => 'code',
                'label' => __('Tax Identifier'),
                'title' => __('Tax Identifier'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $fieldset->addField(
            'zip_is_range',
            'checkbox',
            ['name' => 'zip_is_range', 'label' => __('Zip/Post is Range'), 'value' => '1']
        );

        if (!isset($formData['tax_postcode'])) {
            $formData['tax_postcode'] = $this->_scopeConfig->getValue(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_POSTCODE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        $fieldset->addField(
            'tax_postcode',
            'text',
            [
                'name' => 'tax_postcode',
                'label' => __('Zip/Post Code'),
                'note' => __(
                    "'*' - matches any; 'xyz*' - matches any that begins on 'xyz' and are not longer than %1.",
                    $this->_taxData->getPostCodeSubStringLength()
                )
            ]
        );

        $fieldset->addField(
            'zip_from',
            'text',
            [
                'name' => 'zip_from',
                'label' => __('Range From'),
                'required' => true,
                'maxlength' => 9,
                'class' => 'validate-digits',
                'css_class' => 'hidden'
            ]
        );

        $fieldset->addField(
            'zip_to',
            'text',
            [
                'name' => 'zip_to',
                'label' => __('Range To'),
                'required' => true,
                'maxlength' => 9,
                'class' => 'validate-digits',
                'css_class' => 'hidden'
            ]
        );

        $fieldset->addField(
            'tax_region_id',
            'select',
            ['name' => 'tax_region_id', 'label' => __('State'), 'values' => $regions]
        );

        $fieldset->addField(
            'tax_country_id',
            'select',
            ['name' => 'tax_country_id', 'label' => __('Country'), 'required' => true, 'values' => $countries]
        );

        $fieldset->addField(
            'rate',
            'text',
            [
                'name' => 'rate',
                'label' => __('Rate Percent'),
                'title' => __('Rate Percent'),
                'required' => true,
                'class' => 'validate-not-negative-number'
            ]
        );

        $form->setAction($this->getUrl('tax/rate/save'));
        $form->setUseContainer(true);
        $form->setId(self::FORM_ELEMENT_ID);
        $form->setMethod('post');

        if (!$this->_storeManager->hasSingleStore()) {
            $form->addElement($this->_fieldsetFactory->create()->setLegend(__('Tax Titles')));
        }

        if (isset($formData['zip_is_range']) && $formData['zip_is_range']) {
            list($formData['zip_from'], $formData['zip_to']) = explode('-', $formData['tax_postcode']);
        }
        $form->setValues($formData);
        $this->setForm($form);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('Magento\Framework\View\Element\Template')->setTemplate('Magento_Tax::rate/js.phtml')
        );

        return parent::_prepareForm();
    }

    /**
     * Get Tax Rates Collection
     *
     * @return mixed
     */
    public function getRateCollection()
    {
        if ($this->getData('rate_collection') == null) {
            $items = $this->_taxRateCollection->getItems();
            $rates = [];
            foreach ($items as $rate) {
                $rateData = $rate->getData();
                if (isset($rateData['titles'])) {
                    foreach ($rateData['titles'] as $storeId => $value) {
                        $rateData['title[' . $storeId . ']'] = $value;
                    }
                }
                unset($rateData['titles']);
                $rates[] = $rateData;
            }

            $this->setRateCollection($rates);
        }
        return $this->getData('rate_collection');
    }

    /**
     * Extract tax rate data in a format which is
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
}
