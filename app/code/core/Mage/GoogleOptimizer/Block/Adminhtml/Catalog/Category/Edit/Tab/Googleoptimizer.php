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
 * @package     Mage_GoogleOptimizer
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Optimizer Category Tab
 *
 * @category    Mage
 * @package     Mage_GoogleOptimizer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleOptimizer_Block_Adminhtml_Catalog_Category_Edit_Tab_Googleoptimizer
    extends Mage_Adminhtml_Block_Catalog_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setShowGlobalIcon(true);
    }

    public function getCategory()
    {
        if (!$this->_category) {
            $this->_category = Mage::registry('current_category');
        }
        return $this->_category;
    }

    public function getGoogleOptimizer()
    {
        return $this->getCategory()->getGoogleOptimizerScripts();
    }

    public function _prepareLayout()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Google Optimizer Scripts'))
        );

        if ($this->getCategory()->getStoreId() == '0') {
            Mage::helper('Mage_GoogleOptimizer_Helper_Data')->setStoreId(Mage::app()->getDefaultStoreView());
        } else {
            Mage::helper('Mage_GoogleOptimizer_Helper_Data')->setStoreId($this->getCategory()->getStoreId());
        }

        $disabledScriptsFields = false;
        $values = array();
        if ($this->getGoogleOptimizer() && $this->getGoogleOptimizer()->getData()) {
            $disabledScriptsFields = true;
            $values = $this->getGoogleOptimizer()->getData();
            $checkedUseDefault = true;
            if ($this->getGoogleOptimizer()->getStoreId() == $this->getCategory()->getStoreId()) {
                $checkedUseDefault = false;
                $disabledScriptsFields = false;
                $fieldset->addField('code_id', 'hidden', array('name' => 'code_id'));
            }
            // show 'use default' checkbox if store different for default and product has scripts for default store
            if ($this->getCategory()->getStoreId() != '0') {
                $fieldset->addField('store_flag', 'checkbox',
                    array(
                        'name'  => 'store_flag',
                        'value' => '1',
                        'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Use Default'),
                        'class' => 'checkbox',
                        'required' => false,
                        'onchange' => 'googleOptimizerScopeAction()',
                    )
                )->setIsChecked($checkedUseDefault);
            }
        }

        $fieldset->addField('conversion_page', 'select',
            array(
                'name'  => 'conversion_page',
                'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Conversion Page'),
                'values'=>
                    Mage::getModel('Mage_GoogleOptimizer_Model_Adminhtml_System_Config_Source_Googleoptimizer_Conversionpages')
                        ->toOptionArray(),
                'class' => 'select googleoptimizer validate-googleoptimizer',
                'required' => false,
                'onchange' => 'googleOptimizerConversionPageAction(this)'
            )
        );
        //Mage::getStoreConfigFlag(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL)
        if ($this->getCategory()->getStoreId() == '0' && !Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('conversion_page_url', 'note',
                array(
                    'name'  => 'conversion_page_url',
                    'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Conversion Page URL'),
                    'text' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Please select store view to see the URL')
                )
            );
        } else {
            $fieldset->addField('conversion_page_url', 'text',
                array(
                    'name'  => 'conversion_page_url',
                    'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Conversion Page URL'),
                    'class' => 'input-text',
                    'readonly' => 'readonly',
                    'required' => false,
                    'note' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Please copy and paste this value to experiment edit form')
                )
            );
        }

        $fieldset->addField('export_controls', 'text', array('name' => 'export_controls'));

        $fieldset->addField('control_script', 'textarea',
            array(
                'name'  => 'control_script',
                'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Control Script'),
                'class' => 'textarea googleoptimizer validate-googleoptimizer',
                'required' => false,
            )
        );

        $fieldset->addField('tracking_script', 'textarea',
            array(
                'name'  => 'tracking_script',
                'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Tracking Script'),
                'class' => 'textarea googleoptimizer validate-googleoptimizer',
                'required' => false,
            )
        );

        $fieldset->addField('conversion_script', 'textarea',
            array(
                'name'  => 'conversion_script',
                'label' => Mage::helper('Mage_GoogleOptimizer_Helper_Data')->__('Conversion Script'),
                'class' => 'textarea googleoptimizer validate-googleoptimizer',
                'required' => false,
            )
        );

        if (Mage::helper('Mage_GoogleOptimizer_Helper_Data')->getConversionPagesUrl()
            && $this->getGoogleOptimizer()
            && $this->getGoogleOptimizer()->getConversionPage())
        {
            $form->getElement('conversion_page_url')
                ->setValue(Mage::helper('Mage_GoogleOptimizer_Helper_Data')
                    ->getConversionPagesUrl()->getData($this->getGoogleOptimizer()->getConversionPage())
                );
        }

        if ($disabledScriptsFields) {
            foreach ($fieldset->getElements() as $element) {
                if ($element->getType() == 'textarea' || $element->getType() == 'select') {
                    $element->setDisabled($disabledScriptsFields);
                }
            }
        }

        $fakeEntityAttribute = Mage::getModel('Mage_Catalog_Model_Resource_Eav_Attribute');

        $readonly = $this->getCategory()->getOptimizationReadonly();
        foreach ($fieldset->getElements() as $element) {
            $element->setDisabled($readonly);
            if ($element->getId() != 'store_flag') {
                $element->setEntityAttribute($fakeEntityAttribute);
            }
        }

        $form->getElement('export_controls')->setRenderer(
            $this->getLayout()->createBlock('Mage_GoogleOptimizer_Block_Adminhtml_Catalog_Form_Renderer_Import')
        );

        $form->addValues($values);
        $form->setFieldNameSuffix('googleoptimizer');
        $this->setForm($form);

        return parent::_prepareLayout();
    }

}
