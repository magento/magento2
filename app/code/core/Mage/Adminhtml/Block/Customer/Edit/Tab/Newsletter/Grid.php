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
 * Adminhtml newsletter queue grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_Newsletter_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('queueGrid');
        $this->setDefaultSort('start_at');
        $this->setDefaultDir('desc');

        $this->setUseAjax(true);

        $this->setEmptyText(Mage::helper('Mage_Customer_Helper_Data')->__('No Newsletter Found'));

    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/newsletter', array('_current'=>true));
    }

    protected function _prepareCollection()
    {
        /** @var $collection Mage_Newsletter_Model_Resource_Queue_Collection */
        $collection = Mage::getResourceModel('Mage_Newsletter_Model_Resource_Queue_Collection')
            ->addTemplateInfo()
            ->addSubscriberFilter(Mage::registry('subscriber')->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('queue_id', array(
            'header'    =>  Mage::helper('Mage_Customer_Helper_Data')->__('ID'),
            'align'     =>  'left',
            'index'     =>  'queue_id',
            'width'     =>  10
        ));

        $this->addColumn('start_at', array(
            'header'    =>  Mage::helper('Mage_Customer_Helper_Data')->__('Newsletter Start'),
            'type'      =>  'datetime',
            'align'     =>  'center',
            'index'     =>  'queue_start_at',
            'default'   =>  ' ---- '
        ));

        $this->addColumn('finish_at', array(
            'header'    =>  Mage::helper('Mage_Customer_Helper_Data')->__('Newsletter Finish'),
            'type'      =>  'datetime',
            'align'     =>  'center',
            'index'     =>  'queue_finish_at',
            'gmtoffset' => true,
            'default'   =>  ' ---- '
        ));

        $this->addColumn('letter_sent_at', array(
            'header'    =>  Mage::helper('Mage_Customer_Helper_Data')->__('Newsletter Received'),
            'type'      =>  'datetime',
            'align'     =>  'center',
            'index'     =>  'letter_sent_at',
            'gmtoffset' => true,
            'default'   =>  ' ---- '
        ));

        $this->addColumn('template_subject', array(
            'header'    =>  Mage::helper('Mage_Customer_Helper_Data')->__('Subject'),
            'align'     =>  'center',
            'index'     =>  'template_subject'
        ));

         $this->addColumn('status', array(
            'header'    =>  Mage::helper('Mage_Customer_Helper_Data')->__('Status'),
            'align'     =>  'center',
            'filter'    =>  'Mage_Adminhtml_Block_Customer_Edit_Tab_Newsletter_Grid_Filter_Status',
            'index'     => 'queue_status',
            'renderer'  =>  'Mage_Adminhtml_Block_Customer_Edit_Tab_Newsletter_Grid_Renderer_Status'
        ));

        $this->addColumn('action', array(
            'header'    =>  Mage::helper('Mage_Customer_Helper_Data')->__('Action'),
            'align'     =>  'center',
            'filter'    =>  false,
            'sortable'  =>  false,
            'renderer'  =>  'Mage_Adminhtml_Block_Customer_Edit_Tab_Newsletter_Grid_Renderer_Action'
        ));

        return parent::_prepareColumns();
    }

}
