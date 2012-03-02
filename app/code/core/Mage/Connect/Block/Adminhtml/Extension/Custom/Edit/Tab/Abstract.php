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
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract for extension info tabs
 *
 * @category    Mage
 * @package     Mage_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Connect_Block_Adminhtml_Extension_Custom_Edit_Tab_Abstract
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * TODO
     */
    protected $_addRowButtonHtml;

    /**
     * TODO
     */
    protected $_removeRowButtonHtml;

    /**
     * TODO
     */
    protected $_addFileDepButtonHtml;

    /**
     * TODO
     */
    public function __construct()
    {
        parent::__construct();
        $this->setData(Mage::getSingleton('Mage_Connect_Model_Session')->getCustomExtensionPackageFormData());
    }

    /**
     * TODO   remove ???
     */
    public function initForm()
    {
        return $this;
    }

    /**
     * TODO
     */
    public function getValue($key, $default='')
    {
        $value = $this->getData($key);
        return htmlspecialchars($value ? $value : $default);
    }

    /**
     * TODO
     */
    public function getSelected($key, $value)
    {
        return $this->getData($key)==$value ? 'selected="selected"' : '';
    }

    /**
     * TODO
     */
    public function getChecked($key)
    {
        return $this->getData($key) ? 'checked="checked"' : '';
    }

    /**
     * TODO
     */
    public function getAddRowButtonHtml($container, $template, $title='Add')
    {
        if (!isset($this->_addRowButtonHtml[$container])) {
            $this->_addRowButtonHtml[$container] = $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_Widget_Button')
                    ->setType('button')
                    ->setClass('add')
                    ->setLabel($this->__($title))
                    ->setOnClick("addRow('".$container."', '".$template."')")
                    ->toHtml();
        }
        return $this->_addRowButtonHtml[$container];
    }

    /**
     * TODO
     */
    public function getRemoveRowButtonHtml($selector='span')
    {
        if (!$this->_removeRowButtonHtml) {
            $this->_removeRowButtonHtml = $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_Widget_Button')
                    ->setType('button')
                    ->setClass('delete')
                    ->setLabel($this->__('Remove'))
                    ->setOnClick("removeRow(this, '".$selector."')")
                    ->toHtml();
        }
        return $this->_removeRowButtonHtml;
    }

    public function getAddFileDepsRowButtonHtml($selector='span', $filesClass='files')
    {
        if (!$this->_addFileDepButtonHtml) {
            $this->_addFileDepButtonHtml = $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_Widget_Button')
                    ->setType('button')
                    ->setClass('add')
                    ->setLabel($this->__('Add files'))
                    ->setOnClick("showHideFiles(this, '".$selector."', '".$filesClass."')")
                    ->toHtml();
        }
        return $this->_addFileDepButtonHtml;

    }

    /**
     * Get Tab Label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return '';
    }

    /**
     * Get Tab Title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return '';
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}