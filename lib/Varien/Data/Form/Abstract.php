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
 * @category   Varien
 * @package    Varien_Data
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Abstract class for form, coumn and fieldset
 *
 * @category   Varien
 * @package    Varien_Data
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Varien_Data_Form_Abstract extends Varien_Object
{

    /**
     * Form level elements collection
     *
     * @var Varien_Data_Form_Element_Collection
     */
    protected $_elements;

    /**
     * Element type classes
     *
     * @var unknown_type
     */
    protected $_types = array();

    /**
     * Enter description here...
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->_construct();
    }

    /**
     * Internal constructor, that is called from real constructor
     *
     * Please override this one instead of overriding real __construct constructor
     *
     */
    protected function _construct()
    {
    }

    /**
     * Add element type
     *
     * @param string $type
     * @param string $className
     * @return Varien_Data_Form_Abstract
     */
    public function addType($type, $className)
    {
        $this->_types[$type] = $className;
        return $this;
    }

    /**
     * Get elements collection
     *
     * @return Varien_Data_Form_Element_Collection
     */
    public function getElements()
    {
        if (empty($this->_elements)) {
            $this->_elements = new Varien_Data_Form_Element_Collection($this);
        }
        return $this->_elements;
    }

    /**
     * Disable elements
     *
     * @param boolean $readonly
     * @param boolean $useDisabled
     * @return Varien_Data_Form_Abstract
     */
    public function setReadonly($readonly, $useDisabled = false)
    {
        if ($useDisabled) {
            $this->setDisabled($readonly);
            $this->setData('readonly_disabled', $readonly);
        } else {
            $this->setData('readonly', $readonly);
        }
        foreach ($this->getElements() as $element) {
            $element->setReadonly($readonly, $useDisabled);
        }

        return $this;
    }

    /**
     * Add form element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param bool|string|null $after
     *
     * @return Varien_Data_Form
     */
    public function addElement(Varien_Data_Form_Element_Abstract $element, $after = null)
    {
        $element->setForm($this);
        $this->getElements()->add($element, $after);
        return $this;
    }

    /**
     * Add child element
     *
     * if $after parameter is false - then element adds to end of collection
     * if $after parameter is null - then element adds to befin of collection
     * if $after parameter is string - then element adds after of the element with some id
     *
     * @param   string $elementId
     * @param   string $type
     * @param   array  $config
     * @param   mixed  $after
     * @return Varien_Data_Form_Element_Abstract
     */
    public function addField($elementId, $type, $config, $after=false)
    {
        if (isset($this->_types[$type])) {
            $className = $this->_types[$type];
        } else {
            $className = 'Varien_Data_Form_Element_' . ucfirst(strtolower($type));
        }
        $element = new $className($config);
        $element->setId($elementId);
        $this->addElement($element, $after);
        return $element;
    }

    /**
     * Enter description here...
     *
     * @param string $elementId
     * @return Varien_Data_Form_Abstract
     */
    public function removeField($elementId)
    {
        $this->getElements()->remove($elementId);
        return $this;
    }

    /**
     * Add fieldset
     *
     * @param string $elementId
     * @param array $config
     * @param bool|string|null $after
     * @param bool $isAdvanced
     * @return Varien_Data_Form_Element_Fieldset
     */
    public function addFieldset($elementId, $config, $after = false, $isAdvanced = false)
    {
        $element = new Varien_Data_Form_Element_Fieldset($config);
        $element->setId($elementId);
        $element->setAdvanced($isAdvanced);
        $this->addElement($element, $after);
        return $element;
    }

    /**
     * Add column element
     *
     * @param string $elementId
     * @param array $config
     * @return Varien_Data_Form_Element_Column
     */
    public function addColumn($elementId, $config)
    {
        $element = new Varien_Data_Form_Element_Column($config);
        $element->setForm($this)
            ->setId($elementId);
        $this->addElement($element);
        return $element;
    }

    /**
     * Convert elements to array
     *
     * @param array $arrAttributes
     * @return array
     */
    public function __toArray(array $arrAttributes = array())
    {
        $res = array();
        $res['config']  = $this->getData();
        $res['formElements']= array();
        foreach ($this->getElements() as $element) {
            $res['formElements'][] = $element->toArray();
        }
        return $res;
    }

}
