<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Admin product tax class add form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
declare(strict_types=1);

namespace Magento\Tax\Block\Adminhtml\Rate;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form as FormData;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Block\Adminhtml\Rate\Title\FieldsetFactory;
use Magento\Tax\Controller\RegistryConstants;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Calculation\Rate\Converter;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\TaxRateCollection;

/**
 * Tax rate form.
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Form extends Generic
{
    const FORM_ELEMENT_ID = 'rate-form';

    /**
     * @var null
     */
    protected $_titles = null;

    /**
     * @var string
     */
    protected $_template = 'Magento_Tax::rate/form.phtml';

    /**
     * Tax data
     *
     * @var TaxHelper|null
     */
    protected $_taxData = null;

    /**
     * @var FieldsetFactory
     */
    protected $_fieldsetFactory;

    /**
     * @var Country
     */
    protected $_country;

    /**
     * @var RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var TaxRateRepositoryInterface
     */
    protected $_taxRateRepository;

    /**
     * @var TaxRateCollection
     */
    protected $_taxRateCollection;

    /**
     * @var Converter
     */
    protected $_taxRateConverter;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param RegionFactory $regionFactory
     * @param Country $country
     * @param FieldsetFactory $fieldsetFactory
     * @param TaxHelper $taxData
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param TaxRateCollection $taxRateCollection
     * @param Converter $taxRateConverter
     * @param array $data
     * @param DirectoryHelper|null $directoryHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        RegionFactory $regionFactory,
        Country $country,
        FieldsetFactory $fieldsetFactory,
        TaxHelper $taxData,
        TaxRateRepositoryInterface $taxRateRepository,
        TaxRateCollection $taxRateCollection,
        Converter $taxRateConverter,
        array $data = [],
        ?DirectoryHelper $directoryHelper = null
    ) {
        $this->_regionFactory = $regionFactory;
        $this->_country = $country;
        $this->_fieldsetFactory = $fieldsetFactory;
        $this->_taxData = $taxData;
        $this->_taxRateRepository = $taxRateRepository;
        $this->_taxRateCollection = $taxRateCollection;
        $this->_taxRateConverter = $taxRateConverter;
        $data['directoryHelper'] = $directoryHelper ?? ObjectManager::getInstance()->get(DirectoryHelper::class);
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDestElementId(self::FORM_ELEMENT_ID);
    }

    /**
     * Prepare form before rendering HTML.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $taxRateId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_TAX_RATE_ID);

        try {
            if ($taxRateId) {
                $taxRateDataObject = $this->_taxRateRepository->get($taxRateId);
            }
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
        } catch (NoSuchEntityException $e) {
            //tax rate not found//
        }

        $sessionFormValues = (array)$this->_coreRegistry->registry(RegistryConstants::CURRENT_TAX_RATE_FORM_DATA);
        $formData = isset($taxRateDataObject)
            ? $this->_taxRateConverter->createArrayFromServiceObject($taxRateDataObject)
            : [];
        $formData = array_merge($formData, $sessionFormValues);

        if (isset($formData['zip_is_range']) && $formData['zip_is_range'] && !isset($formData['tax_postcode'])) {
            $formData['tax_postcode'] = $formData['zip_from'] . '-' . $formData['zip_to'];
        }

        /** @var FormData $form */
        $form = $this->_formFactory->create();

        $countries = $this->_country->toOptionArray(false, 'US');
        unset($countries[0]);

        if (!isset($formData['tax_country_id'])) {
            $formData['tax_country_id'] = $this->_scopeConfig->getValue(
                Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                ScopeInterface::SCOPE_STORE
            );
        }

        if (!isset($formData['tax_region_id'])) {
            $formData['tax_region_id'] = $this->_scopeConfig->getValue(
                Config::CONFIG_XML_PATH_DEFAULT_REGION,
                ScopeInterface::SCOPE_STORE
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
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => $legend, 'class' => 'admin__scope-old form-inline']
        );

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
            [
                'name' => 'zip_is_range',
                'label' => __('Zip/Post is Range'),
                'value' => '1',
                'class' => 'zip-is-range-checkbox'
            ]
        );

        if (!isset($formData['tax_postcode'])) {
            $formData['tax_postcode'] = $this->_scopeConfig->getValue(
                Config::CONFIG_XML_PATH_DEFAULT_POSTCODE,
                ScopeInterface::SCOPE_STORE
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
                ),
                'class' => 'validate-length maximum-length-' . $this->_taxData->getPostCodeSubStringLength()
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
            $this->getLayout()->createBlock(
                Template::class
            )->setTemplate('Magento_Tax::rate/js.phtml')
        );

        return parent::_prepareForm();
    }
}
