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
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer addresses forms
 *
 * @category   Magento
 * @package    Magento_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

class Addresses extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_template = 'tab/addresses.phtml';

    /**
     * Adminhtml addresses
     *
     * @var \Magento\Backend\Helper\Addresses
     */
    protected $_adminhtmlAddresses = null;

    /**
     * @var \Magento\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\Customer\Helper\Data
     */
    protected $_customerHelper;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Data\FormFactory $formFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Customer\Model\Renderer\RegionFactory $regionFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Model\FormFactory $customerFactory
     * @param \Magento\Core\Model\System\Store $systemStore
     * @param \Magento\Backend\Helper\Addresses $adminhtmlAddresses
     * @param \Magento\Customer\Helper\Data $customerHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Registry $registry,
        \Magento\Data\FormFactory $formFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Json\EncoderInterface $jsonEncoder,
        \Magento\Customer\Model\Renderer\RegionFactory $regionFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\FormFactory $customerFactory,
        \Magento\Core\Model\System\Store $systemStore,
        \Magento\Backend\Helper\Addresses $adminhtmlAddresses,
        \Magento\Customer\Helper\Data $customerHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = array()
    ) {
        $this->_customerHelper = $customerHelper;
        $this->_coreData = $coreData;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_adminhtmlAddresses = $adminhtmlAddresses;
        $this->_regionFactory = $regionFactory;
        $this->_addressFactory = $addressFactory;
        $this->_customerFactory = $customerFactory;
        $this->_systemStore = $systemStore;
        $this->_directoryHelper = $directoryHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getRegionsUrl()
    {
        return $this->getUrl('directory/json/countryRegion');
    }

    protected function _prepareLayout()
    {
        $this->addChild('delete_button', 'Magento\Backend\Block\Widget\Button', array(
            'label'  => __('Delete Address'),
            'name'   => 'delete_address',
            'element_name' => 'delete_address',
            'disabled' => $this->isReadonly(),
            'class'  => 'delete' . ($this->isReadonly() ? ' disabled' : '')
        ));
        $this->addChild('add_address_button', 'Magento\Backend\Block\Widget\Button', array(
            'label'  => __('Add New Address'),
            'id'     => 'add_address_button',
            'name'   => 'add_address_button',
            'element_name' => 'add_address_button',
            'disabled' => $this->isReadonly(),
            'class'  => 'add'  . ($this->isReadonly() ? ' disabled' : '')
        ));
        $this->addChild('cancel_button', 'Magento\Backend\Block\Widget\Button', array(
            'label'  => __('Cancel'),
            'id'     => 'cancel_add_address'.$this->getTemplatePrefix(),
            'name'   => 'cancel_address',
            'element_name' => 'cancel_address',
            'class'  => 'cancel delete-address'  . ($this->isReadonly() ? ' disabled' : ''),
            'disabled' => $this->isReadonly()
        ));
        return parent::_prepareLayout();
    }

    /**
     * Check block is readonly.
     *
     * @return boolean
     */
    public function isReadonly()
    {
        $customer = $this->_coreRegistry->registry('current_customer');
        return $customer->isReadonly();
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    /**
     * Initialize form object
     *
     * @return \Magento\Customer\Block\Adminhtml\Edit\Tab\Addresses
     */
    public function initForm()
    {
        /* @var $customer \Magento\Customer\Model\Customer */
        $customer = $this->_coreRegistry->registry('current_customer');

        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('address_fieldset', array(
            'legend'    => __("Edit Customer's Address"))
        );

        $addressModel = $this->_addressFactory->create();
        $addressModel->setCountryId($this->_coreData->getDefaultCountry($customer->getStore()));
        /** @var $addressForm \Magento\Customer\Model\Form */
        $addressForm = $this->_customerFactory->create();
        $addressForm->setFormCode('adminhtml_customer_address')
            ->setEntity($addressModel)
            ->initDefaultValues();

        $attributes = $addressForm->getAttributes();
        if (isset($attributes['street'])) {
            $this->_adminhtmlAddresses
                ->processStreetAttribute($attributes['street']);
        }
        foreach ($attributes as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $attribute->setFrontendLabel(__($attribute->getFrontend()->getLabel()));
            $attribute->unsIsVisible();
        }
        $this->_setFieldset($attributes, $fieldset);

        $regionElement = $form->getElement('region');

        if ($regionElement) {
            $regionElement->setRenderer($this->_regionFactory->create());
        }

        $regionElement = $form->getElement('region_id');
        if ($regionElement) {
            $regionElement->setNoDisplay(true);
        }

        $country = $form->getElement('country_id');
        if ($country) {
            $country->addClass('countries');
        }

        if ($this->isReadonly()) {
            foreach ($addressModel->getAttributes() as $attribute) {
                $element = $form->getElement($attribute->getAttributeCode());
                if ($element) {
                    $element->setReadonly(true, true);
                }
            }
        }

        $customerStoreId = null;
        if ($customer->getId()) {
            $customerStoreId = $this->_storeManager->getWebsite($customer->getWebsiteId())
                ->getDefaultStore()
                ->getId();
        }

        $prefixElement = $form->getElement('prefix');
        if ($prefixElement) {
            $prefixOptions = $this->_customerHelper->getNamePrefixOptions($customerStoreId);
            if (!empty($prefixOptions)) {
                $fieldset->removeField($prefixElement->getId());
                $prefixField = $fieldset->addField($prefixElement->getId(),
                    'select',
                    $prefixElement->getData(),
                    '^'
                );
                $prefixField->setValues($prefixOptions);
            }
        }

        $suffixElement = $form->getElement('suffix');
        if ($suffixElement) {
            $suffixOptions = $this->_customerHelper->getNameSuffixOptions($customerStoreId);
            if (!empty($suffixOptions)) {
                $fieldset->removeField($suffixElement->getId());
                $suffixField = $fieldset->addField($suffixElement->getId(),
                    'select',
                    $suffixElement->getData(),
                    $form->getElement('lastname')->getId()
                );
                $suffixField->setValues($suffixOptions);
            }
        }

        $addressCollection = $customer->getAddresses();
        $this->assign('customer', $customer);
        $this->assign('addressCollection', $addressCollection);
        $form->setValues($addressModel->getData());
        $this->setForm($form);

        return $this;
    }

    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('cancel_button');
    }

    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_address_button');
    }

    /**
     * Return predefined additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return array(
            'file'      => 'Magento\Customer\Block\Adminhtml\Form\Element\File',
            'image'     => 'Magento\Customer\Block\Adminhtml\Form\Element\Image',
            'boolean'   => 'Magento\Customer\Block\Adminhtml\Form\Element\Boolean',
        );
    }

    /**
     * Add specified values to name prefix element values
     *
     * @param string|int|array $values
     * @return \Magento\Customer\Block\Adminhtml\Edit\Tab\Addresses
     */
    public function addValuesToNamePrefixElement($values)
    {
        if ($this->getForm() && $this->getForm()->getElement('prefix')) {
            $this->getForm()->getElement('prefix')->addElementValues($values);
        }
        return $this;
    }

    /**
     * Add specified values to name suffix element values
     *
     * @param string|int|array $values
     * @return \Magento\Customer\Block\Adminhtml\Edit\Tab\Addresses
     */
    public function addValuesToNameSuffixElement($values)
    {
        if ($this->getForm() && $this->getForm()->getElement('suffix')) {
            $this->getForm()->getElement('suffix')->addElementValues($values);
        }
        return $this;
    }

    /**
     * Returns the template prefix
     *
     * @return string
     */
    public function getTemplatePrefix()
    {
        return '_template_';
    }

    /**
     * Return array with countries associated to possible websites
     *
     * @return array
     */
    public function getDefaultCountries()
    {
        $websites = $this->_systemStore->getWebsiteValuesForForm(false, true);
        $result = array();
        foreach ($websites as $website) {
            $result[$website['value']] = $this->_storeManager->getWebsite($website['value'])
                ->getConfig(
                    \Magento\Core\Helper\Data::XML_PATH_DEFAULT_COUNTRY
                );
        }

        return $result;
    }

    /**
     * Return ISO2 country codes, which have optional Zip/Postal pre-configured
     *
     * @return array
     */
    public function getOptionalZipCountries()
    {
        return $this->_directoryHelper->getCountriesWithOptionalZip();
    }

    /**
     * Returns the list of countries, for which region is required
     *
     * @return array
     */
    public function getRequiredStateForCountries()
    {
        return $this->_directoryHelper->getCountriesWithStatesRequired();
    }

    /**
     * eturn, whether non-required state should be shown
     *
     * @return int 1 if should be shown, and 0 if not.
     */
    public function getShowAllRegions()
    {
        return (string)$this->_directoryHelper->isShowNonRequiredState() ? 1 : 0;
    }

    /**
     * Encode the $data into JSON format.
     *
     * @param object|array $data
     * @return string
     */
    public function jsonEncode($data)
    {
        return $this->_jsonEncoder->encode($data);
    }
}
