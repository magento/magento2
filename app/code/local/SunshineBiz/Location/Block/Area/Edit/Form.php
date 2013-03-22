<?php

/**
 * SunshineBiz_Location area edit form block
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Block_Area_Edit_Form extends SunshineBiz_Location_Block_Widget_Form {

    public function _construct() {

        parent::_construct();
        $this->setId('area_form');

        $this->setTitle($this->_helper->__('Area Information'));
    }

    protected function _prepareForm() {

        $model = Mage::registry('locations_area');
        $form = new Varien_Data_Form(
                array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post')
        );
        $form->setHtmlIdPrefix('area_');

        $fieldset = $form->addFieldset(
                'base_fieldset', array('legend' => $this->_helper->__('Area Information'))
        );

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
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
        } else {
            if (!$model->hasData('is_active')) {
                $model->setIsActive(1);
            }
        }

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

        $fieldset->addField('is_active', 'select', array(
            'name' => 'is_active',
            'label' => $this->_helper->__('Status'),
            'id' => 'is_active',
            'title' => $this->_helper->__('Status'),
            'class' => 'input-select',
            'style' => 'width: 80px',
            'options' => array(
                '1' => $this->_helper->__('Active'),
                '0' => $this->_helper->__('Inactive')
            ),
        ));

        $fieldset->addField('country_id', 'select', array(
            'name' => 'country_id',
            'label' => $this->_helper->__('Country'),
            'id' => 'country_id',
            'title' => $this->_helper->__('Country'),
            'class' => 'input-select',
            'onchange' => 'locationChanged(this, \'' . $this->getUrl('*/json/countryRegion') . '\',  \'area_region_id\')'
        ))->setValues(Mage::getResourceModel('Mage_Directory_Model_Resource_Country_Collection')
                        ->load()->toOptionArray());

        $options = Mage::getModel('Mage_Directory_Model_Country')
                ->setId($model->getCountryId())
                ->getRegions()
                ->toOptionArray();
        $fieldset->addField('region_id', 'select', array(
            'name' => 'region_id',
            'label' => $this->_helper->__('Regions'),
            'id' => 'region_id',
            'title' => $this->_helper->__('Regions'),
            'class' => 'input-select',
            'onchange' => 'locationChanged(this, \'' . $this->getUrl('*/json/regionArea') . '\',  \'area_parent_id\')',
            'required' => true,
        ))->setValues($options);

        $options = array();
        if ($model->getRegionId()) {
            $options = Mage::getModel('SunshineBiz_Location_Model_Region')
                    ->setId($model->getRegionId())
                    ->getAreas()
                    ->toOptionArray();
        } else {
            array_unshift($options, array('value' => '0', 'label' => ''));
        }
        $fieldset->addField('parent_id', 'select', array(
            'name' => 'parent_id',
            'label' => $this->_helper->__('Parent'),
            'id' => 'parent_id',
            'title' => $this->_helper->__('Parent'),
            'class' => 'input-select'
        ))->setValues($options);

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getChangeUrl() {

        $model = Mage::registry('locations_area');

        return $this->getUrl('*/*/edit', array(
                    'id' => $model->getId()
                ));
    }

}