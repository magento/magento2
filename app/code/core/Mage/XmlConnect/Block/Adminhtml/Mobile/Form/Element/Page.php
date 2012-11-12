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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect page form element
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Form_Element_Page
    extends Varien_Data_Form_Element_Abstract
{
    /**
     * Init page element
     *
     * @param array $attributes
     */
    protected function _construct($attributes=array())
    {
        parent::_construct($attributes);
        $this->setType('page');
    }

    /**
     * Setting stored data to page element
     *
     * @param array $conf
     */
    public function initFields($conf)
    {
        $this->addElement(new Varien_Data_Form_Element_Text(array(
            'name'  => $conf['name'] . '[label]',
            'class' => 'label onclick_text',
        )));

        $this->addElement(new Varien_Data_Form_Element_Select(array(
            'name'      => $conf['name'] . '[id]',
            'values'    => $conf['values'],
        )));
    }

    /**
     * Add form element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param boolean|string $after also can be '^'
     * @return Varien_Data_Form
     */
    public function addElement(Varien_Data_Form_Element_Abstract $element, $after = false)
    {
        $element->setId($element->getData('name'));
        parent::addElement($element, $after);
    }

    /**
     * Getter for Label field
     * fetching first element as label
     *
     * @param string $idSuffix
     * @return string
     */
    public function getLabelHtml($idSuffix = '')
    {
        list($label, $element) = $this->getElements();
        return $label->toHtml();
    }

    /**
     * Getter for second part of rendered field ("selectbox" and "delete button")
     * fetching second element as <element code>
     *
     * @return string
     */
    public function getElementHtml()
    {
        list($label, $element) = $this->getElements();
        return $element->toHtml()
            . '</td><td class="label" style="width: 5em">'
            . '<button class="scalable save onclick_button" value="&minus;"><span><span><span>'
            . Mage::helper('Mage_XmlConnect_Helper_Data')->__('Delete')
            . '</span></span></span></button>';
    }
}
