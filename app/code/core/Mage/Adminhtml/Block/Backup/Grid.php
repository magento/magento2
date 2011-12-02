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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml backups grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Backup_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        $this->setSaveParametersInSession(true);
        $this->setId('backupsGrid');
        $this->setDefaultSort('time', 'desc');
    }

    /**
     * Init backups collection
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getSingleton('Mage_Backup_Model_Fs_Collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Configuration of grid
     */
    protected function _prepareColumns()
    {
        $url7zip = Mage::helper('Mage_Adminhtml_Helper_Data')->__('The archive can be uncompressed with <a href="%s">%s</a> on Windows systems', 'http://www.7-zip.org/', '7-Zip');

        $this->addColumn('time', array(
            'header'    => Mage::helper('Mage_Backup_Helper_Data')->__('Time'),
            'index'     => 'date_object',
            'type'      => 'datetime',
        ));

        $this->addColumn('size', array(
            'header'    => Mage::helper('Mage_Backup_Helper_Data')->__('Size, Bytes'),
            'index'     => 'size',
            'type'      => 'number',
            'sortable'  => false,
            'filter'    => false
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('Mage_Backup_Helper_Data')->__('Type'),
            'type'      => 'options',
            'options'   => array('db' => Mage::helper('Mage_Backup_Helper_Data')->__('DB')),
            'index'     =>'type'
        ));

        $this->addColumn('download', array(
            'header'    => Mage::helper('Mage_Backup_Helper_Data')->__('Download'),
            'format'    => '<a href="' . $this->getUrl('*/*/download', array('time' => '$time', 'type' => '$type')) .'">gz</a> &nbsp; <small>('.$url7zip.')</small>',
            'index'     => 'type',
            'sortable'  => false,
            'filter'    => false
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('Mage_Backup_Helper_Data')->__('Action'),
            'type'      => 'action',
            'width'     => '80px',
            'filter'    => false,
            'sortable'  => false,
            'actions'   => array(array(
                'url'       => $this->getUrl('*/*/delete', array('time' => '$time', 'type' => '$type')),
                'caption'   => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Delete'),
                'confirm'   => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Are you sure you want to do this?')
            )),
            'index'     => 'type',
            'sortable'  => false
        ));

        return $this;
    }

}
