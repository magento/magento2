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
 * Adminhtml backup page content block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Backup extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('backup/list.phtml');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setChild('createButton',
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')
                ->setData(array(
                    'label' => Mage::helper('Mage_Backup_Helper_Data')->__('Create Backup'),
                    'onclick' => "window.location.href='" . $this->getUrl('*/*/create') . "'",
                    'class'  => 'task'
                ))
        );
        $this->setChild('backupsGrid',
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Backup_Grid')
        );
    }

    public function getCreateButtonHtml()
    {
        return $this->getChildHtml('createButton');
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('backupsGrid');
    }
}
