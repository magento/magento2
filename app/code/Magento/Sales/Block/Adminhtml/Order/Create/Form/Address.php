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
namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Customer\Service\V1\Data\AddressConverter;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Order create address form
 */
class Address extends \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm
{
    /**
     * Customer form factory
     *
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $_customerFormFactory;

    /**
     * Json encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * Customer helper
     *
     * @var \Magento\Customer\Helper\Data
     */
    protected $_customerHelper;

    /**
     * Address service
     *
     * @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface
     */
    protected $_addressService;

    /**
     * Address helper
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $_addressHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory
     * @param \Magento\Customer\Helper\Data $customerHelper
     * @param \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory,
        \Magento\Customer\Helper\Data $customerHelper,
        \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService,
        \Magento\Customer\Helper\Address $addressHelper,
        array $data = array()
    ) {
        $this->_customerHelper = $customerHelper;
        $this->_coreData = $coreData;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_customerFormFactory = $customerFormFactory;
        $this->_addressService = $addressService;
        $this->_addressHelper = $addressHelper;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $formFactory, $data);
    }

    /**
     * Get config
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve current customer address DATA collection.
     *
     * @return \Magento\Customer\Service\V1\Data\Address[]
     */
    public function getAddressCollection()
    {
        if ($this->getCustomerId()) {
            return $this->_addressService->getAddresses($this->getCustomerId());
        }
        return array();
    }

    /**
     * Return Customer Address Collection as JSON
     *
     * @return string
     */
    public function getAddressCollectionJson()
    {
        $defaultCountryId = $this->_coreData->getDefaultCountry($this->getStore());
        $emptyAddressForm = $this->_customerFormFactory->create(
            'customer_address',
            'adminhtml_customer_address',
            array(\Magento\Customer\Service\V1\Data\Address::KEY_COUNTRY_ID => $defaultCountryId)
        );
        $data = array(0 => $emptyAddressForm->outputData(\Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON));
        foreach ($this->getAddressCollection() as $addressData) {
            $addressForm = $this->_customerFormFactory->create(
                'customer_address',
                'adminhtml_customer_address',
                AddressConverter::toFlatArray($addressData)
            );
            $data[$addressData->getId()] = $addressForm->outputData(
                \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON
            );
        }
        return $this->_jsonEncoder->encode($data);
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareForm()
    {
        $fieldset = $this->_form->addFieldset('main', array('no_container' => true));

        $addressForm = $this->_customerFormFactory->create('customer_address', 'adminhtml_customer_address');
        $attributes = $addressForm->getAttributes();
        $this->_addAttributesToForm($attributes, $fieldset);

        $prefixElement = $this->_form->getElement('prefix');
        if ($prefixElement) {
            $prefixOptions = $this->_customerHelper->getNamePrefixOptions($this->getStore());
            if (!empty($prefixOptions)) {
                $fieldset->removeField($prefixElement->getId());
                $prefixField = $fieldset->addField($prefixElement->getId(), 'select', $prefixElement->getData(), '^');
                $prefixField->setValues($prefixOptions);
                if ($this->getAddressId()) {
                    $prefixField->addElementValues($this->getAddress()->getPrefix());
                }
            }
        }

        $suffixElement = $this->_form->getElement('suffix');
        if ($suffixElement) {
            $suffixOptions = $this->_customerHelper->getNameSuffixOptions($this->getStore());
            if (!empty($suffixOptions)) {
                $fieldset->removeField($suffixElement->getId());
                $suffixField = $fieldset->addField(
                    $suffixElement->getId(),
                    'select',
                    $suffixElement->getData(),
                    $this->_form->getElement('lastname')->getId()
                );
                $suffixField->setValues($suffixOptions);
                if ($this->getAddressId()) {
                    $suffixField->addElementValues($this->getAddress()->getSuffix());
                }
            }
        }


        $regionElement = $this->_form->getElement('region_id');
        if ($regionElement) {
            $regionElement->setNoDisplay(true);
        }

        $this->_form->setValues($this->getFormValues());

        if ($this->_form->getElement('country_id')->getValue()) {
            $countryId = $this->_form->getElement('country_id')->getValue();
            $this->_form->getElement('country_id')->setValue(null);
            foreach ($this->_form->getElement('country_id')->getValues() as $country) {
                if ($country['value'] == $countryId) {
                    $this->_form->getElement('country_id')->setValue($countryId);
                }
            }
        }
        if (is_null($this->_form->getElement('country_id')->getValue())) {
            $this->_form->getElement('country_id')->setValue($this->_coreData->getDefaultCountry($this->getStore()));
        }

        // Set custom renderer for VAT field if needed
        $vatIdElement = $this->_form->getElement('vat_id');
        if ($vatIdElement && $this->getDisplayVatValidationButton() !== false) {
            $vatIdElement->setRenderer(
                $this->getLayout()->createBlock(
                    'Magento\Customer\Block\Adminhtml\Sales\Order\Address\Form\Renderer\Vat'
                )->setJsVariablePrefix(
                    $this->getJsVariablePrefix()
                )
            );
        }

        return $this;
    }

    /**
     * Add additional data to form element
     *
     * @param AbstractElement $element
     * @return $this
     */
    protected function _addAdditionalFormElementData(AbstractElement $element)
    {
        if ($element->getId() == 'region_id') {
            $element->setNoDisplay(true);
        }
        return $this;
    }

    /**
     * Return customer address id
     *
     * @return false
     */
    public function getAddressId()
    {
        return false;
    }

    /**
     * Represent customer address in 'online' format.
     *
     * @param \Magento\Customer\Service\V1\Data\Address $addressData
     * @return string
     */
    public function getAddressAsString($addressData)
    {
        $formatTypeRenderer = $this->_addressHelper->getFormatTypeRenderer('oneline');
        $result = '';
        if ($formatTypeRenderer) {
            $result = $formatTypeRenderer->renderArray(AddressConverter::toFlatArray($addressData));
        }
        return $this->escapeHtml($result);
    }
}
