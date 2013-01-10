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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product form weight field helper
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Weight_Renderer extends Varien_Data_Form_Element_Text
{
    const VIRTUAL_FIELD_HTML_ID = 'weight_and_type_switcher';

    /**
     * Is virtual checkbox element
     *
     * @var Varien_Data_Form_Element_Checkbox
     */
    protected $_virtual;

    /**
     * Catalog helper
     *
     * @var Mage_Catalog_Helper_Product
     */
    protected $_helper;

    public function __construct(array $data = array())
    {
        $this->_helper = isset($data['helper']) ? $data['helper'] : Mage::helper('Mage_Catalog_Helper_Product');
        $this->_virtual = isset($data['element'])
            ? $data['element']
            : Mage::getModel('Varien_Data_Form_Element_Checkbox');
        $this->_virtual->setId(self::VIRTUAL_FIELD_HTML_ID)->setName('is_virtual')
            ->setLabel($this->_helper->getTypeSwitcherControlLabel());
        parent::__construct($data);
    }

    /**
     * Add Is Virtual checkbox html to weight field
     *
     * @return string
     */
    public function getElementHtml()
    {
        if (!$this->getForm()->getDataObject()->getTypeInstance()->hasWeight()) {
            $this->_virtual->setChecked('checked');
        }
        return parent::getElementHtml() . $this->_virtual->getElementHtml() . $this->_virtual->getLabelHtml();
    }

    /**
     * Set form for both fields
     *
     * @param Varien_Data_Form $form
     * @return Varien_Data_Form
     */
    public function setForm($form)
    {
        $this->_virtual->setForm($form);
        return parent::setForm($form);
    }

}
