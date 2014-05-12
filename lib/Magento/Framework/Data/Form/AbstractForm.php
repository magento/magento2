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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Data\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Collection;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Column;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Fieldset;

/**
 * Abstract class for form, coumn and fieldset
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class AbstractForm extends \Magento\Framework\Object
{
    /**
     * Form level elements collection
     *
     * @var Collection
     */
    protected $_elements;

    /**
     * Element type classes
     *
     * @var array
     */
    protected $_types = array();

    /**
     * @var Factory
     */
    protected $_factoryElement;

    /**
     * @var CollectionFactory
     */
    protected $_factoryCollection;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param array $data
     */
    public function __construct(Factory $factoryElement, CollectionFactory $factoryCollection, $data = array())
    {
        $this->_factoryElement = $factoryElement;
        $this->_factoryCollection = $factoryCollection;
        parent::__construct($data);
        $this->_construct();
    }

    /**
     * Internal constructor, that is called from real constructor
     *
     * Please override this one instead of overriding real __construct constructor
     *
     * @return void
     */
    protected function _construct()
    {
    }

    /**
     * Add element type
     *
     * @param string $type
     * @param string $className
     * @return $this
     */
    public function addType($type, $className)
    {
        $this->_types[$type] = $className;
        return $this;
    }

    /**
     * Get elements collection
     *
     * @return Collection
     */
    public function getElements()
    {
        if (empty($this->_elements)) {
            $this->_elements = $this->_factoryCollection->create(array('container' => $this));
        }
        return $this->_elements;
    }

    /**
     * Disable elements
     *
     * @param boolean $readonly
     * @param boolean $useDisabled
     * @return $this
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
     * @param AbstractElement $element
     * @param bool|string|null $after
     * @return $this
     */
    public function addElement(AbstractElement $element, $after = null)
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
     * @param   bool|string|null  $after
     * @return AbstractElement
     */
    public function addField($elementId, $type, $config, $after = false)
    {
        if (isset($this->_types[$type])) {
            $type = $this->_types[$type];
        }
        $element = $this->_factoryElement->create($type, array('data' => $config));
        $element->setId($elementId);
        $this->addElement($element, $after);
        return $element;
    }

    /**
     * Enter description here...
     *
     * @param string $elementId
     * @return $this
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
     * @return Fieldset
     */
    public function addFieldset($elementId, $config, $after = false, $isAdvanced = false)
    {
        $element = $this->_factoryElement->create('fieldset', array('data' => $config));
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
     * @return Column
     */
    public function addColumn($elementId, $config)
    {
        $element = $this->_factoryElement->create('column', array('data' => $config));
        $element->setForm($this)->setId($elementId);
        $this->addElement($element);
        return $element;
    }

    /**
     * Convert elements to array
     *
     * @param array $arrAttributes
     * @return array
     */
    public function convertToArray(array $arrAttributes = array())
    {
        $res = array();
        $res['config'] = $this->getData();
        $res['formElements'] = array();
        foreach ($this->getElements() as $element) {
            $res['formElements'][] = $element->toArray();
        }
        return $res;
    }
}
