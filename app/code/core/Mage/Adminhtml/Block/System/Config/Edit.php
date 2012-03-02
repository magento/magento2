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
 * Config edit page
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Config_Edit extends Mage_Adminhtml_Block_Widget
{
    const DEFAULT_SECTION_BLOCK = 'Mage_Adminhtml_Block_System_Config_Form';

    protected $_section;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('system/config/edit.phtml');

        $sectionCode = $this->getRequest()->getParam('section');
        $sections = Mage::getSingleton('Mage_Adminhtml_Model_Config')->getSections();

        $this->_section = $sections->$sectionCode;

        $this->setTitle((string)$this->_section->label);
        $this->setHeaderCss((string)$this->_section->header_css);
    }

    protected function _prepareLayout()
    {
        $this->setChild('save_button',
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')
                ->setData(array(
                    'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Save Config'),
                    'onclick'   => 'configForm.submit()',
                    'class' => 'save',
                ))
        );
        return parent::_prepareLayout();
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true));
    }

    public function initForm()
    {
        /*
        $this->setChild('dwstree',
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Config_Dwstree')
                ->initTabs()
        );
        */

        $blockName = (string)$this->_section->frontend_model;
        if (empty($blockName)) {
            $blockName = self::DEFAULT_SECTION_BLOCK;
        }
        $this->setChild('form',
            $this->getLayout()->createBlock($blockName)
                ->initForm()
        );
        return $this;
    }


}
