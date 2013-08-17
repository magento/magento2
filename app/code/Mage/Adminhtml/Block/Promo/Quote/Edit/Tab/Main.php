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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping Cart Price Rule General Information Tab
 *
 * @category Mage
 * @package Mage_Adminhtml
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Main
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('Mage_SalesRule_Helper_Data')->__('Rule Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('Mage_SalesRule_Helper_Data')->__('Rule Information');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('current_promo_quote_rule');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend' => Mage::helper('Mage_SalesRule_Helper_Data')->__('General Information'))
        );

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', array(
                'name' => 'rule_id',
            ));
        }

        $fieldset->addField('product_ids', 'hidden', array(
            'name' => 'product_ids',
        ));

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Rule Name'),
            'title' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Rule Name'),
            'required' => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Description'),
            'title' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Description'),
            'style' => 'height: 100px;',
        ));

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('Mage_SalesRule_Helper_Data')->__('Status'),
            'title'     => Mage::helper('Mage_SalesRule_Helper_Data')->__('Status'),
            'name'      => 'is_active',
            'required' => true,
            'options'    => array(
                '1' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Active'),
                '0' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Inactive'),
            ),
        ));

        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

        if (Mage::app()->isSingleStoreMode()) {
            $websiteId = Mage::app()->getStore(true)->getWebsiteId();
            $fieldset->addField('website_ids', 'hidden', array(
                'name'     => 'website_ids[]',
                'value'    => $websiteId
            ));
            $model->setWebsiteIds($websiteId);
        } else {
            $field = $fieldset->addField('website_ids', 'multiselect', array(
                'name'     => 'website_ids[]',
                'label'     => Mage::helper('Mage_SalesRule_Helper_Data')->__('Websites'),
                'title'     => Mage::helper('Mage_SalesRule_Helper_Data')->__('Websites'),
                'required' => true,
                'values'   => Mage::getSingleton('Mage_Core_Model_System_Store')->getWebsiteValuesForForm(),
            ));
            $renderer = $this->getLayout()->createBlock('Mage_Backend_Block_Store_Switcher_Form_Renderer_Fieldset_Element');
            $field->setRenderer($renderer);
        }

        $customerGroups = Mage::getResourceModel('Mage_Customer_Model_Resource_Group_Collection')->load()->toOptionArray();
        $found = false;

        foreach ($customerGroups as $group) {
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array(
                'value' => 0,
                'label' => Mage::helper('Mage_SalesRule_Helper_Data')->__('NOT LOGGED IN'))
            );
        }

        $fieldset->addField('customer_group_ids', 'multiselect', array(
            'name'      => 'customer_group_ids[]',
            'label'     => Mage::helper('Mage_SalesRule_Helper_Data')->__('Customer Groups'),
            'title'     => Mage::helper('Mage_SalesRule_Helper_Data')->__('Customer Groups'),
            'required'  => true,
            'values'    => Mage::getResourceModel('Mage_Customer_Model_Resource_Group_Collection')->toOptionArray(),
        ));

        $couponTypeFiled = $fieldset->addField('coupon_type', 'select', array(
            'name'       => 'coupon_type',
            'label'      => Mage::helper('Mage_SalesRule_Helper_Data')->__('Coupon'),
            'required'   => true,
            'options'    => Mage::getModel('Mage_SalesRule_Model_Rule')->getCouponTypes(),
        ));

        $couponCodeFiled = $fieldset->addField('coupon_code', 'text', array(
            'name' => 'coupon_code',
            'label' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Coupon Code'),
            'required' => true,
        ));

        $autoGenerationCheckbox = $fieldset->addField('use_auto_generation', 'checkbox', array(
            'name'  => 'use_auto_generation',
            'label' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Use Auto Generation'),
            'note'  => Mage::helper('Mage_SalesRule_Helper_Data')->__('If you select and save the rule you will be able to generate multiple coupon codes.'),
            'onclick' => 'handleCouponsTabContentActivity()',
            'checked' => (int)$model->getUseAutoGeneration() > 0 ? 'checked' : ''
        ));

        $autoGenerationCheckbox->setRenderer(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Main_Renderer_Checkbox')
        );

        $usesPerCouponFiled = $fieldset->addField('uses_per_coupon', 'text', array(
            'name' => 'uses_per_coupon',
            'label' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Uses per Coupon'),
        ));

        $fieldset->addField('uses_per_customer', 'text', array(
            'name' => 'uses_per_customer',
            'label' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Uses per Customer'),
        ));

        $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_LocaleInterface::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array(
            'name'   => 'from_date',
            'label'  => Mage::helper('Mage_SalesRule_Helper_Data')->__('From Date'),
            'title'  => Mage::helper('Mage_SalesRule_Helper_Data')->__('From Date'),
            'image'  => $this->getViewFileUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'date_format'  => $dateFormat
        ));
        $fieldset->addField('to_date', 'date', array(
            'name'   => 'to_date',
            'label'  => Mage::helper('Mage_SalesRule_Helper_Data')->__('To Date'),
            'title'  => Mage::helper('Mage_SalesRule_Helper_Data')->__('To Date'),
            'image'  => $this->getViewFileUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'date_format'  => $dateFormat
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name' => 'sort_order',
            'label' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Priority'),
        ));

        $fieldset->addField('is_rss', 'select', array(
            'label'     => Mage::helper('Mage_SalesRule_Helper_Data')->__('Public In RSS Feed'),
            'title'     => Mage::helper('Mage_SalesRule_Helper_Data')->__('Public In RSS Feed'),
            'name'      => 'is_rss',
            'options'   => array(
                '1' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Yes'),
                '0' => Mage::helper('Mage_SalesRule_Helper_Data')->__('No'),
            ),
        ));

        if(!$model->getId()){
            //set the default value for is_rss feed to yes for new promotion
            $model->setIsRss(1);
        }

        $form->setValues($model->getData());

        $autoGenerationCheckbox->setValue(1);

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        //$form->setUseContainer(true);

        $this->setForm($form);

        // field dependencies
        $this->setChild('form_after', $this->getLayout()
            ->createBlock('Mage_Adminhtml_Block_Widget_Form_Element_Dependence')
            ->addFieldMap($couponTypeFiled->getHtmlId(), $couponTypeFiled->getName())
            ->addFieldMap($couponCodeFiled->getHtmlId(), $couponCodeFiled->getName())
            ->addFieldMap($autoGenerationCheckbox->getHtmlId(), $autoGenerationCheckbox->getName())
            ->addFieldMap($usesPerCouponFiled->getHtmlId(), $usesPerCouponFiled->getName())
            ->addFieldDependence(
                $couponCodeFiled->getName(),
                $couponTypeFiled->getName(),
                Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
            ->addFieldDependence(
                $autoGenerationCheckbox->getName(),
                $couponTypeFiled->getName(),
                Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
            ->addFieldDependence(
                $usesPerCouponFiled->getName(),
                $couponTypeFiled->getName(),
                Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
        );

        Mage::dispatchEvent('adminhtml_promo_quote_edit_tab_main_prepare_form', array('form' => $form));

        return parent::_prepareForm();
    }
}
