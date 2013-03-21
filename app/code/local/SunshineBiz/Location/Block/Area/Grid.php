<?php

/**
 * SunshineBiz_Location area grid block
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Block_Area_Grid extends SunshineBiz_Location_Block_Widget_Grid {

    public function _construct() {
        parent::_construct();
        $this->setId('locationsAreaGrid');
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('SunshineBiz_Location_Model_Resource_Area_Collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('id', array(
            'header' => $this->_helper->__('ID'),
            'type' => 'number',
            'index' => 'id'
        ));

        $this->addColumn('default_name', array(
            'header' => $this->_helper->__('Default Name'),
            'index' => 'default_name'
        ));

        $this->addColumn('name', array(
            'header' => $this->_helper->__("%s Name", $this->_helper->getLocaleLabel()),
            'index' => 'name'
        ));

        $this->addColumn('is_active', array(
            'header' => $this->_helper->__('Status'),
            'index' => 'is_active',
            'type' => 'options',
            'options' => array(
                '1' => $this->_helper->__('Active'),
                '0' => $this->_helper->__('Inactive')
            ),
        ));

        $this->addColumn('region_id', array(
            'header' => $this->_helper->__('Regions'),
            'index' => 'region_id',
            'filter' => 'SunshineBiz_Location_Block_Widget_Grid_Column_Filter_Region',
            'renderer' => 'SunshineBiz_Location_Block_Widget_Grid_Column_Renderer_Region',
        ));

        $this->addColumn('parent_id', array(
            'header' => $this->_helper->__('Parent'),
            'index' => 'parent_id',
            'filter' => 'SunshineBiz_Location_Block_Area_Grid_Column_Filter_Parent',
            'renderer' => 'SunshineBiz_Location_Block_Widget_Grid_Column_Renderer_Area',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {

        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('area');
        $this->getMassactionBlock()->setData('header', '123');
        $key = $this->getMassactionBlock()->getFormFieldNameInternal();
        if (!$this->getRequest()->has($key)) {
            $this->getRequest()->setParam($key, '5,6,7,8,9,10');
        }

        $statuses = array(
            '1' => $this->_helper->__('Active'),
            '0' => $this->_helper->__('Inactive')
        );
        $this->getMassactionBlock()->addItem('status', array(
            'label' => $this->_helper->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'is_active',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $this->_helper->__('Status'),
                    'values' => $statuses
                )
            )
        ));

        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}