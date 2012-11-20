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
 * Tab for Flurry Analytics Management
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Edit_Tab_Flurryanalytics
    extends Mage_XmlConnect_Block_Adminhtml_Mobile_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_pages;

    /**
     * Constructor
     * Setting view options
     */
    protected function _construct()
    {
        parent::_construct();
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
        $yesNoValues = Mage::getModel('Mage_Backend_Model_Config_Source_Yesno')->toOptionArray();

        $fieldset = $form->addFieldset('flurryAnalytics', array('legend' => $this->__('Flurry Analytics')));

        if (isset($data['conf[native][merchantFlurryTracking][isActive]'])) {
            $isActiveValue = $data['conf[native][merchantFlurryTracking][isActive]'];
        } else {
            $isActiveValue = '0';
        }

        $enabled = $fieldset->addField('conf/native/merchantFlurryTracking/isActive', 'select', array(
            'label'     => $this->__('Enable Flurry Analytics'),
            'name'      => 'conf[native][merchantFlurryTracking][isActive]',
            'values'    => $yesNoValues,
            'note'      => $this->__('Enable Flurry Analytics for the merchant.'),
            'value'     => $isActiveValue
        ));

        $flurryAnalyticsUrl = $this->escapeHtml(
            Mage::getStoreConfig('xmlconnect/flurry_analytics/statistics_url')
        );

        $fieldset->addField('flurry_analytics_link', 'link', array(
            'title'     => $this->__('Flurry Analytics Site'),
            'label'     => $this->__('Flurry Analytics Site'),
            'value'     => $flurryAnalyticsUrl,
            'href'      => $flurryAnalyticsUrl,
            'target'    => '__blank',
            'note'      => $this->__('You can watch statistics here.'),
        ));

        if (isset($data['conf[native][merchantFlurryTracking][accountId]'])) {
            $accountIdValue = $data['conf[native][merchantFlurryTracking][accountId]'];
        } else {
            $accountIdValue = '';
        }

        $flurryApiCode = $fieldset->addField('conf/native/merchantFlurryTracking/accountId', 'text', array(
            'label'     => $this->__('Flurry API Code'),
            'name'      => 'conf[native][merchantFlurryTracking][accountId]',
            'enabled'   => true,
            'required'  => true,
            'value'     => $accountIdValue
        ));

        // field dependencies
        $this->setChild('form_after', $this->getLayout()
            ->createBlock('Mage_Adminhtml_Block_Widget_Form_Element_Dependence')
            ->addFieldMap($flurryApiCode->getHtmlId(), $flurryApiCode->getName())
            ->addFieldMap($enabled->getHtmlId(), $enabled->getName())
            ->addFieldDependence(
                $flurryApiCode->getName(),
                $enabled->getName(),
                1
        ));
        return parent::_prepareForm();
    }

    /**
     * Tab label getter
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Analytics');
    }

    /**
     * Tab title getter
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Flurry Analytics');
    }

    /**
     * Check if tab can be shown
     *
     * @return bool
     */
    public function canShowTab()
    {
        $deviceType = Mage::helper('Mage_XmlConnect_Helper_Data')->getDeviceType();
        return (bool) !Mage::getSingleton('Mage_Adminhtml_Model_Session')->getNewApplication()
            && $deviceType == Mage_XmlConnect_Helper_Data::DEVICE_TYPE_IPHONE;
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
