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

/**
 * Adminhtml newsletter subscribers grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Newsletter_Subscriber_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Application instance
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Constructor
     *
     * Set main configuration of grid
     */
    protected function _construct()
    {
        $this->_app = Mage::app();

        parent::_construct();
        $this->setId('subscriberGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('subscriber_id', 'desc');
    }

    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceSingleton('Mage_Newsletter_Model_Resource_Subscriber_Collection');
        /* @var $collection Mage_Newsletter_Model_Resource_Subscriber_Collection */
        $collection
            ->showCustomerInfo(true)
            ->addSubscriberTypeField()
            ->showStoreInfo();

        if($this->getRequest()->getParam('queue', false)) {
            $collection->useQueue(Mage::getModel('Mage_Newsletter_Model_Queue')
                ->load($this->getRequest()->getParam('queue')));
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('subscriber_id', array(
            'header'    => Mage::helper('Mage_Newsletter_Helper_Data')->__('ID'),
            'index'     => 'subscriber_id'
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('Mage_Newsletter_Helper_Data')->__('Email'),
            'index'     => 'subscriber_email'
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('Mage_Newsletter_Helper_Data')->__('Type'),
            'index'     => 'type',
            'type'      => 'options',
            'options'   => array(
                1  => Mage::helper('Mage_Newsletter_Helper_Data')->__('Guest'),
                2  => Mage::helper('Mage_Newsletter_Helper_Data')->__('Customer')
            )
        ));

        $this->addColumn('firstname', array(
            'header'    => Mage::helper('Mage_Newsletter_Helper_Data')->__('Customer First Name'),
            'index'     => 'customer_firstname',
            'default'   =>    '----'
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('Mage_Newsletter_Helper_Data')->__('Customer Last Name'),
            'index'     => 'customer_lastname',
            'default'   =>    '----'
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('Mage_Newsletter_Helper_Data')->__('Status'),
            'index'     => 'subscriber_status',
            'type'      => 'options',
            'options'   => array(
                Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE   => Mage::helper('Mage_Newsletter_Helper_Data')->__('Not Activated'),
                Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED   => Mage::helper('Mage_Newsletter_Helper_Data')->__('Subscribed'),
                Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED => Mage::helper('Mage_Newsletter_Helper_Data')->__('Unsubscribed'),
                Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED => Mage::helper('Mage_Newsletter_Helper_Data')->__('Unconfirmed'),
            )
        ));

        if (!$this->_app->isSingleStoreMode()) {
            $this->addColumn('website', array(
                'header'    => Mage::helper('Mage_Newsletter_Helper_Data')->__('Website'),
                'index'     => 'website_id',
                'type'      => 'options',
                'options'   => $this->_getWebsiteOptions()
            ));

            $this->addColumn('group', array(
                'header'    => Mage::helper('Mage_Newsletter_Helper_Data')->__('Store'),
                'index'     => 'group_id',
                'type'      => 'options',
                'options'   => $this->_getStoreGroupOptions()
            ));

            $this->addColumn('store', array(
                'header'    => Mage::helper('Mage_Newsletter_Helper_Data')->__('Store View'),
                'index'     => 'store_id',
                'type'      => 'options',
                'options'   => $this->_getStoreOptions()
            ));
        }

        $this->addExportType('*/*/exportCsv', Mage::helper('Mage_Customer_Helper_Data')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('Mage_Customer_Helper_Data')->__('Excel XML'));
        return parent::_prepareColumns();
    }

    /**
     * Convert OptionsValue array to Options array
     *
     * @param array $optionsArray
     * @return array
     */
    protected function _getOptions($optionsArray)
    {
        $options = array();
        foreach ($optionsArray as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    /**
     * Retrieve Website Options array
     *
     * @return array
     */
    protected function _getWebsiteOptions()
    {
        return Mage::getModel('Mage_Core_Model_System_Store')->getWebsiteOptionHash();
    }

    /**
     * Retrieve Store Group Options array
     *
     * @return array
     */
    protected function _getStoreGroupOptions()
    {
        return Mage::getModel('Mage_Core_Model_System_Store')->getStoreGroupOptionHash();
    }

    /**
     * Retrieve Store Options array
     *
     * @return array
     */
    protected function _getStoreOptions()
    {
        return Mage::getModel('Mage_Core_Model_System_Store')->getStoreOptionHash();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('subscriber_id');
        $this->getMassactionBlock()->setFormFieldName('subscriber');

        $this->getMassactionBlock()->addItem('unsubscribe', array(
             'label'        => Mage::helper('Mage_Newsletter_Helper_Data')->__('Unsubscribe'),
             'url'          => $this->getUrl('*/*/massUnsubscribe')
        ));

        $this->getMassactionBlock()->addItem('delete', array(
             'label'        => Mage::helper('Mage_Newsletter_Helper_Data')->__('Delete'),
             'url'          => $this->getUrl('*/*/massDelete')
        ));

        return $this;
    }
}
