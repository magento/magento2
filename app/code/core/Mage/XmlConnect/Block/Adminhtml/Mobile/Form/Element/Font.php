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
 * XmlConnect font form element
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Form_Element_Font
    extends Varien_Data_Form_Element_Abstract
{
    /**
     * Init font element
     *
     * @param array $attributes
     */
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('font');
    }

    /**
     * Setting stored data to Font element
     *
     * @param array $conf
     */
    public function initFields($conf)
    {
        $name = $conf['name'];

        $this->addElement(new Varien_Data_Form_Element_Select(array(
            'name' => $name . '[name]',
            'values' => $conf['fontNames'],
            'style' => 'width: 206px; margin: 0',
        )));

        $this->addElement(new Varien_Data_Form_Element_Select(array(
            'name' => $name . '[size]',
            'values' => $conf['fontSizes'],
            'style' => 'width: 70px; margin: 0',
        )));

        $this->addElement(new Mage_XmlConnect_Block_Adminhtml_Mobile_Form_Element_Color(array(
            'name'  => $name . '[color]',
            'style' => 'width: 60px; margin: 0'
        )));
    }

    /**
     * Add form element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param bool|string $after also can be '^'
     * @return Varien_Data_Form
     */
    public function addElement(Varien_Data_Form_Element_Abstract $element, $after = false)
    {
        $element->setId($element->getData('name'));
        $element->setNoSpan(true);
        parent::addElement($element, $after);
    }

    /**
     * Get Rendered Element Html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $elementsArray = array();
        foreach ($this->getElements() as $element) {
            $elementsArray[] .= $element->toHtml();
        }
        return $elementsArray[0]
            . $elementsArray[1]
            . '</td><td class="label" style="width: 2em !important">'
            . $elementsArray[2];
    }
}
