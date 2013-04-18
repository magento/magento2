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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Block_Adminhtml_Subscriber_Grid extends Mage_Backend_Block_Widget_Grid_Extended
{
    public function _construct()
    {
        parent::_construct();
        $this->setId('subscriberGrid');
        $this->setDefaultSort('subscriber_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        Mage::getSingleton('Mage_Webhook_Model_Subscriber_Config')->updateSubscriberCollection();

        $collection = Mage::getModel('Mage_Webhook_Model_Subscriber')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => $this->__('ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'subscriber_id',
        ));

        $this->addColumn('name', array(
            'header'    => $this->__('Name'),
            'align'     => 'left',
            'index'     => 'name',
        ));

        $this->addColumn('version', array(
            'header'    => $this->__('Version'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'version',
        ));

        $this->addColumn('endpoint_url', array(
            'header'    => $this->__('Endpoint URL'),
            'align'     => 'left',
            'index'     => 'endpoint_url',
        ));

        $this->addColumn('status', array(
            'header'    => $this->__('Status'),
            'align'     =>'left',
            'index'     => 'status',
            'type'      => 'options',
            'width'     => '100px',
            'options'   => $this->_getStatusOptions()
        ));

        $this->addColumn('action', array(
            'header'    =>  Mage::helper('Mage_Webhook_Helper_Data')->__('Action'),
            'align'     =>  'left',
            'width'     => '80px',
            'filter'    =>  false,
            'sortable'  =>  false,
            'renderer'  =>  'Mage_Webhook_Block_Adminhtml_Subscriber_Grid_Renderer_Action'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _getStatusOptions()
    {
        return array(
            Mage_Webhook_Model_Subscriber::STATUS_ACTIVE => $this->__('Active'),
            Mage_Webhook_Model_Subscriber::STATUS_REVOKED => $this->__('Revoked'),
            Mage_Webhook_Model_Subscriber::STATUS_INACTIVE => $this->__('Inactive'),
        );
    }
}
