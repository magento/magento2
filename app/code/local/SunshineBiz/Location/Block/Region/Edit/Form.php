<?php

/**
 * SunshineBiz_Location region edit form block
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Block_Region_Edit_Form extends SunshineBiz_Location_Block_Widget_Form {

    public function _construct() {

        parent::_construct();
        $this->setId('region_form');

        $this->setTitle($this->_helper->__('Region Information'));
    }

    protected function _prepareForm() {

        $model = Mage::registry('locations_region');
        $form = new Varien_Data_Form(
                array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post')
        );
        $form->setHtmlIdPrefix('region_');

        $fieldset = $form->addFieldset(
                'base_fieldset', array('legend' => $this->_helper->__('Region Information'))
        );

        if ($model->getId()) {
            $fieldset->addField('region_id', 'hidden', array(
                'name' => 'region_id',
            ));

            $locales = Mage::app()->getLocale()->getTranslatedOptionLocales();
            array_unshift($locales, array('value' => '', 'label' => $this->_helper->__('Default Locale')));
            $fieldset->addField('locale', 'select', array(
                'name' => 'locale',
                'label' => $this->_helper->__('Locale'),
                'onchange' => 'localeChanged(this, \'' . $this->_getChangeUrl() . '\');',
                'id' => 'locale',
                'title' => $this->_helper->__('Locale'),
                'class' => 'input-select'
            ))->setValues($locales);
        }

        $fieldset->addField('code', 'text', array(
            'name' => 'code',
            'label' => $this->_helper->__('Code'),
            'id' => 'code',
            'title' => $this->_helper->__('Code'),
        ));

        $inherit = false;
        $canUseDefault = false;
        $data = '';
        $id = 'default_name';
        $name = 'default_name';
        $helper = 'Default Name';
        $label = '';
        $required = true;
        if ($model->getId()) {
            if ($model->getLocale()) {
                if ($model->getLocaleName()) {
                    $data = $model->getLocaleName();
                    $id = 'name';
                } else {
                    $data = $model->getDefaultName();
                    $inherit = true;
                }
                $canUseDefault = true;
                $helper = '%s Name';
                $name = 'name';
                $label = $this->_helper->getLocaleLabel($model->getLocale());
                $required = false;
            } else {
                $data = $model->getDefaultName();
            }
        }

        $fieldset->addField($id, 'text', array(
            'name' => $name,
            'label' => $this->_helper->__($helper, $label),
            'value' => $data,
            'inherit' => $inherit,
            'scope' => 'locale',
            'scope_label' => $this->_helper->__('[Locale]'),
            'can_use_default_value' => $canUseDefault,
            'required' => $required,
        ))->setRenderer(Mage::getBlockSingleton('SunshineBiz_Location_Block_Widget_Form_Field_Renderer_Locale'));

        $fieldset->addField('country_id', 'select', array(
            'name' => 'country_id',
            'label' => $this->_helper->__('Country'),
            'id' => 'country_id',
            'title' => $this->_helper->__('Country'),
            'class' => 'input-select',
            'required' => true,
        ))->setValues(Mage::getResourceModel('Mage_Directory_Model_Resource_Country_Collection')
                        ->load()->toOptionArray(false));

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getChangeUrl() {

        $model = Mage::registry('locations_region');

        return $this->getUrl('*/*/edit', array(
                    'id' => $model->getId()
                ));
    }

}