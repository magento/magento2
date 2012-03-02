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
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tab for Payments Management
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Edit_Tab_Payment
    extends Mage_XmlConnect_Block_Adminhtml_Mobile_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_pages;

    /**
     * Constructor
     * Setting view options
     */
    public function __construct()
    {
        parent::__construct();
        $this->setShowGlobalIcon(true);
    }

    /**
     * Prepare form before rendering HTML
     * Setting Form Fieldsets and fields
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $this->setForm($form);

        $data = Mage::helper('Mage_XmlConnect_Helper_Data')->getApplication()->getFormData();
        $yesNoValues = Mage::getModel('Mage_Adminhtml_Model_System_Config_Source_Yesno')->toOptionArray();

        $fieldset = $form->addFieldset('onepage_checkout', array('legend' => $this->__('Standard Checkout')));

        if (isset($data['conf[native][defaultCheckout][isActive]'])) {
            $checkoutStatus = $data['conf[native][defaultCheckout][isActive]'];
        } else {
            $checkoutStatus = '1';
        }

        $fieldset->addField('conf/native/defaultCheckout/isActive', 'select', array(
            'label'     => $this->__('Enable Standard Checkout'),
            'name'      => 'conf[native][defaultCheckout][isActive]',
            'values'    => $yesNoValues,
            'note'      => $this->__('Standard Checkout uses the checkout methods provided by Magento. Only inline payment methods are supported. (e.g PayPal Direct,  Authorize.Net, etc.)'),
            'value'     => $checkoutStatus
        ));

        $deviceType = Mage::helper('Mage_XmlConnect_Helper_Data')->getDeviceType();
        switch ($deviceType) {
            case Mage_XmlConnect_Helper_Data::DEVICE_TYPE_IPHONE:
            case Mage_XmlConnect_Helper_Data::DEVICE_TYPE_IPAD:
                /**
                 * PayPal MEP management
                 */
                $fieldsetPaypal = $form->addFieldset('paypal_mep_checkout', array(
                    'legend' => $this->__('PayPal Mobile Embedded Payment (MEP)')
                ));

                $paypalMepIsAvailable = Mage::getModel('Mage_XmlConnect_Model_Payment_Method_Paypal_Mep')
                    ->isAvailable(null);

                $paypalActive = 0;
                if (isset($data['conf[native][paypal][isActive]'])) {
                    $paypalActive = (int)($data['conf[native][paypal][isActive]'] && $paypalMepIsAvailable);
                }

                $paypalConfigurationUrl = $this->escapeHtml(
                    $this->getUrl('adminhtml/system_config/edit', array('section' => 'paypal'))
                );

                $activateMepMethodNote = $this->__('To activate PayPal MEP payment method activate Express checkout first. ');

                $businessAccountNote = $this->__('MEP is PayPal\'s native checkout experience for the iPhone. You can choose to use MEP alongside standard checkout, or use it as your only checkout method for Magento mobile. PayPal MEP requires a <a href="%s">PayPal business account</a>', $paypalConfigurationUrl);

                $paypalActiveField = $fieldsetPaypal->addField('conf/native/paypal/isActive', 'select', array(
                    'label'     => $this->__('Activate PayPal Checkout'),
                    'name'      => 'conf[native][paypal][isActive]',
                    'note'      => (!$paypalMepIsAvailable ? $activateMepMethodNote : $businessAccountNote),
                    'values'    => $yesNoValues,
                    'value'     => $paypalActive,
                    'disabled'  => !$paypalMepIsAvailable
                ));

                if (isset($data['conf[special][merchantLabel]'])) {
                    $merchantLabelValue = $data['conf[special][merchantLabel]'];
                } else {
                    $merchantLabelValue = '';
                }
                $merchantlabelField = $fieldsetPaypal->addField('conf/special/merchantLabel', 'text', array(
                    'name'      => 'conf[special][merchantLabel]',
                    'label'     => $this->__('Merchant Label'),
                    'title'     => $this->__('Merchant Label'),
                    'required'  => true,
                    'value'     => $merchantLabelValue
                ));

                if (isset($data['config_data[payment][paypalmep/allowspecific]'])) {
                    $paypalMepAllow = (int) $data['config_data[payment][paypalmep/allowspecific]'];
                } else {
                    $paypalMepAllow = 0;
                }
                $paypalMepAllowSpecific = $fieldsetPaypal->addField(
                    'config_data/paypalmep/allowspecific',
                    'select',
                    array(
                        'name'      => 'config_data[payment:paypalmep/allowspecific]',
                        'label'     => $this->__('Payment Applicable From'),
                        'values' => array(
                            array('value' => 0, 'label' => $this->__('All Allowed Countries')),
                            array('value' => 1, 'label' => $this->__('Specific Countries'))
                        ),
                        'value' => $paypalMepAllow
                ));

                $countries = Mage::getModel('Mage_Adminhtml_Model_System_Config_Source_Country')->toOptionArray(true);

                if (empty($data['config_data[payment][paypalmep/allowspecific]'])) {
                    $countrySelected = array();
                } else {
                    $countrySelected = explode(',', $data['config_data[payment][paypalmep/applicable]']);
                }

                $paypalMepApplicable = $fieldsetPaypal->addField(
                    'config_data/paypalmep/applicable',
                    'multiselect',
                    array(
                        'name'  => 'config_data[payment:paypalmep/applicable]',
                        'label' => $this->__('Countries Payment Applicable From'),
                        'values' => $countries,
                        'value' => $countrySelected
                ));

                // field dependencies
                $this->setChild('form_after', $this->getLayout()
                    ->createBlock('Mage_Adminhtml_Block_Widget_Form_Element_Dependence')
                    ->addFieldMap($paypalMepAllowSpecific->getHtmlId(), $paypalMepAllowSpecific->getName())
                    ->addFieldMap($paypalMepApplicable->getHtmlId(), $paypalMepApplicable->getName())
                    ->addFieldMap($merchantlabelField->getHtmlId(), $merchantlabelField->getName())
                    ->addFieldMap($paypalActiveField->getHtmlId(), $paypalActiveField->getName())
                    ->addFieldDependence(
                        $paypalMepApplicable->getName(),
                        $paypalMepAllowSpecific->getName(),
                        1
                    )
                    ->addFieldDependence(
                        $paypalMepAllowSpecific->getName(),
                        $paypalActiveField->getName(),
                        1
                    )
                    ->addFieldDependence(
                        $paypalMepApplicable->getName(),
                        $paypalActiveField->getName(),
                        1
                    )
                    ->addFieldDependence(
                        $merchantlabelField->getName(),
                        $paypalActiveField->getName(),
                        1
                    )
                );
                break;
            case Mage_XmlConnect_Helper_Data::DEVICE_TYPE_ANDROID:
                /**
                 * PayPal MECL management
                 */
                if (Mage::app()->isSingleStoreMode()
                    || Mage::helper('Mage_XmlConnect_Helper_Data')->getApplication()->getId()
                ) {
                    $paypalMeclIsAvailable = Mage::getModel('Mage_XmlConnect_Model_Payment_Method_Paypal_Mecl')
                        ->isAvailable();
                    $activateMeclMethodNote = $this->__('You need to enable PayPal Express Checkout first from the Payment configuration before enabling PayPal MECL.');
                } else {
                    $paypalMeclIsAvailable = false;
                    $activateMeclMethodNote = $this->__('Please create and save an application first.');
                }

                $fieldsetMecl = $form->addFieldset('paypal_mecl_checkout', array(
                    'legend' => $this->__('PayPal Mobile Express Checkout Library (MECL)')
                ));

                $meclAccountNote = $this->__('PayPal MECL is the mobile version of PayPal\'s Express Checkout service. You can choose to use MECL alongside standard checkout, or use it as your only checkout method for Magento Mobile.');

                $paypalMeclActive = 0;
                if (isset($data['config_data[payment][paypalmecl_is_active]'])) {
                    $paypalMeclActive = (int) $data['config_data[payment][paypalmecl_is_active]'];
                }

                $fieldsetMecl->addField('config_data/paypalmecl_is_active', 'select', array(
                    'label'     => $this->__('Activate PayPal MECL'),
                    'name'      => 'config_data[payment:paypalmecl_is_active]',
                    'note'      => (!$paypalMeclIsAvailable ? $activateMeclMethodNote : $meclAccountNote),
                    'values'    => $yesNoValues,
                    'value'     => $paypalMeclActive,
                    'disabled'  => !$paypalMeclIsAvailable
                ));

                /**
                 * PayPal MEP management
                 */
                $fieldsetPaypal = $form->addFieldset('paypal_mep_checkout', array(
                    'legend' => $this->__('PayPal Mobile Embedded Payment (MEP)')
                ));
                $fieldsetPaypal->addField('paypal_note', 'note', array(
                    'label' => $this->__('Notice'),
                    'text'  => $this->__('Currently, PayPal MEP is not available for the Android application')
                ));
                break;
            default:
                Mage::throwException(
                    $this->__('Device doesn\'t recognized: "%s". Unable to load preview model.', $deviceType)
                );
                break;
        }

        return parent::_prepareForm();
    }

    /**
     * Tab label getter
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Payment Methods');
    }

    /**
     * Tab title getter
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Payment Methods');
    }

    /**
     * Check if tab can be shown
     *
     * @return bool
     */
    public function canShowTab()
    {
        return (bool) !Mage::getSingleton('Mage_Adminhtml_Model_Session')->getNewApplication();
    }

    /**
     * Check if tab hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
