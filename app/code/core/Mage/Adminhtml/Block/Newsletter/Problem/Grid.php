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
 * Adminhtml newsletter problem grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Newsletter_Problem_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('problemGrid');
        $this->setSaveParametersInSession(true);
        $this->setMessageBlockVisibility(true);
        $this->setUseAjax(true);
        $this->setEmptyText(Mage::helper('Mage_Newsletter_Helper_Data')->__('No problems found.'));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('Mage_Newsletter_Model_Resource_Problem_Collection')
            ->addSubscriberInfo()
            ->addQueueInfo();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('checkbox', array(
             'sortable' 	=> false,
            'filter'	=> 'Mage_Adminhtml_Block_Newsletter_Problem_Grid_Filter_Checkbox',
            'renderer'	=> 'Mage_Adminhtml_Block_Newsletter_Problem_Grid_Renderer_Checkbox',
            'width'		=> '20px'
        ));

        $this->addColumn('problem_id', array(
            'header' => Mage::helper('Mage_Newsletter_Helper_Data')->__('ID'),
            'index'  => 'problem_id',
            'width'	 => '50px'
        ));

        $this->addColumn('subscriber', array(
            'header' => Mage::helper('Mage_Newsletter_Helper_Data')->__('Subscriber'),
            'index'  => 'subscriber_id',
            'format' => '#$subscriber_id $customer_name ($subscriber_email)'
        ));

        $this->addColumn('queue_start', array(
            'header' => Mage::helper('Mage_Newsletter_Helper_Data')->__('Queue Date Start'),
            'index'  => 'queue_start_at',
            'gmtoffset' => true,
            'type'	 => 'datetime'
        ));

        $this->addColumn('queue', array(
            'header' => Mage::helper('Mage_Newsletter_Helper_Data')->__('Queue Subject'),
            'index'  => 'template_subject'
        ));

        $this->addColumn('problem_code', array(
            'header' => Mage::helper('Mage_Newsletter_Helper_Data')->__('Error Code'),
            'index'  => 'problem_error_code',
            'type'   => 'number'
        ));

        $this->addColumn('problem_text', array(
            'header' => Mage::helper('Mage_Newsletter_Helper_Data')->__('Error Text'),
            'index'  => 'problem_error_text'
        ));
        return parent::_prepareColumns();
    }
}// Class Mage_Adminhtml_Block_Newsletter_Problem_Grid END
