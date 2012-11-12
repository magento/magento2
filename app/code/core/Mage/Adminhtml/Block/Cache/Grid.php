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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Block_Cache_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_invalidatedTypes = array();
    /**
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('cache_grid');
        $this->_filterVisibility = false;
        $this->_pagerVisibility  = false;
        $this->_invalidatedTypes = Mage::app()->getCacheInstance()->getInvalidatedTypes();
    }

    /**
     * Prepare grid collection
     */
    protected function _prepareCollection()
    {
        $collection = new Varien_Data_Collection();
        foreach (Mage::app()->getCacheInstance()->getTypes() as $type) {
            $collection->addItem($type);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     */
    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();
        $this->addColumn('cache_type', array(
            'header'    => $this->__('Cache Type'),
            'width'     => '180',
            'align'     => 'left',
            'index'     => 'cache_type',
            'sortable'  => false,
        ));

        $this->addColumn('description', array(
            'header'    => $this->__('Description'),
            'align'     => 'left',
            'index'     => 'description',
            'sortable'  => false,
        ));

        $this->addColumn('tags', array(
            'header'    => $this->__('Associated Tags'),
            'align'     => 'left',
            'index'     => 'tags',
            'width'     => '180',
            'sortable'  => false,
        ));

        $this->addColumn('status', array(
            'header'    => $this->__('Status'),
            'width'     => '120',
            'align'     => 'left',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(0 => $this->__('Disabled'), 1 => $this->__('Enabled')),
            'frame_callback' => array($this, 'decorateStatus')
        ));

//        $this->addColumn('action',
//            array(
//                'header'    =>  $this->__('Action'),
//                'width'     => '100',
//                'type'      => 'action',
//                'getter'    => 'getId',
//                'actions'   => array(
//                    array(
//                        'caption'   => $this->__('Refresh'),
//                        'url'       => array('base'=> '*/*/refresh'),
//                        'field'     => 'type'
//                    ),
//                ),
//                'filter'    => false,
//                'sortable'  => false,
//                'is_system' => true,
//        ));

        return parent::_prepareColumns();
    }

    /**
     * Decorate status column values
     *
     * @return string
     */
    public function decorateStatus($value, $row, $column, $isExport)
    {
        $class = '';
        if (isset($this->_invalidatedTypes[$row->getId()])) {
            $cell = '<span class="grid-severity-minor"><span>'.$this->__('Invalidated').'</span></span>';
        } else {
            if ($row->getStatus()) {
                $cell = '<span class="grid-severity-notice"><span>'.$value.'</span></span>';
            } else {
                $cell = '<span class="grid-severity-critical"><span>'.$value.'</span></span>';
            }
        }
        return $cell;
    }

    /**
     * Get row edit url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return false;
        //return $this->getUrl('*/*/edit', array('type'=>$row->getId()));
    }

    /**
     * Add mass-actions to grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('types');

        $modeOptions = Mage::getModel('Mage_Index_Model_Process')->getModesOptions();

        $this->getMassactionBlock()->addItem('enable', array(
            'label'         => Mage::helper('Mage_Index_Helper_Data')->__('Enable'),
            'url'           => $this->getUrl('*/*/massEnable'),
        ));
        $this->getMassactionBlock()->addItem('disable', array(
            'label'    => Mage::helper('Mage_Index_Helper_Data')->__('Disable'),
            'url'      => $this->getUrl('*/*/massDisable'),
        ));
        $this->getMassactionBlock()->addItem('refresh', array(
            'label'    => Mage::helper('Mage_Index_Helper_Data')->__('Refresh'),
            'url'      => $this->getUrl('*/*/massRefresh'),
            'selected' => true,
        ));

        return $this;
    }
}
