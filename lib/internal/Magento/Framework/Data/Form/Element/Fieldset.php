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
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Data\Form;
use Magento\Framework\Escaper;

/**
 * Form fieldset
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Fieldset extends AbstractElement
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = array()
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_renderer = Form::getFieldsetRenderer();
        $this->setType('fieldset');
        if (isset($data['advancedSection'])) {
            $this->setAdvancedLabel($data['advancedSection']);
        }
    }

    /**
     * Get elements html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '<fieldset id="' . $this->getHtmlId() . '"' . $this->serialize(
            array('class')
        ) . $this->_getUiId() . '>' . "\n";
        if ($this->getLegend()) {
            $html .= '<legend ' . $this->_getUiId('legend') . '>' . $this->getLegend() . '</legend>' . "\n";
        }
        $html .= $this->getChildrenHtml();
        $html .= '</fieldset>' . "\n";
        $html .= $this->getAfterElementHtml();
        return $html;
    }

    /**
     * Get Children element's array
     *
     * @return AbstractElement[]
     */
    public function getChildren()
    {
        $elements = array();
        foreach ($this->getElements() as $element) {
            if ($element->getType() != 'fieldset') {
                $elements[] = $element;
            }
        }
        return $elements;
    }

    /**
     * Get Children element's html
     *
     * @return string
     */
    public function getChildrenHtml()
    {
        return $this->_elementsToHtml($this->getChildren());
    }

    /**
     * Get Basic elements' array
     *
     * @return AbstractElement[]
     */
    public function getBasicChildren()
    {
        $elements = array();
        foreach ($this->getElements() as $element) {
            if (!$element->isAdvanced()) {
                $elements[] = $element;
            }
        }
        return $elements;
    }

    /**
     * Get Basic elements' html in sorted order
     *
     * @return string
     */
    public function getBasicChildrenHtml()
    {
        return $this->_elementsToHtml($this->getBasicChildren());
    }

    /**
     * Get Number of Basic Children
     *
     * @return int
     */
    public function getCountBasicChildren()
    {
        return count($this->getBasicChildren());
    }

    /**
     * Get Advanced elements'
     *
     * @return array
     */
    public function getAdvancedChildren()
    {
        $elements = array();
        foreach ($this->getElements() as $element) {
            if ($element->isAdvanced()) {
                $elements[] = $element;
            }
        }
        return $elements;
    }

    /**
     * Get Advanced elements' html in sorted order
     *
     * @return string
     */
    public function getAdvancedChildrenHtml()
    {
        return $this->_elementsToHtml($this->getAdvancedChildren());
    }

    /**
     * Whether fieldset contains advance section
     *
     * @return bool
     */
    public function hasAdvanced()
    {
        foreach ($this->getElements() as $element) {
            if ($element->isAdvanced()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get SubFieldset
     *
     * @return AbstractElement[]
     */
    public function getSubFieldset()
    {
        $elements = array();
        foreach ($this->getElements() as $element) {
            if ($element->getType() == 'fieldset' && !$element->isAdvanced()) {
                $elements[] = $element;
            }
        }
        return $elements;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getSubFieldsetHtml()
    {
        return $this->_elementsToHtml($this->getSubFieldset());
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getDefaultHtml()
    {
        $html = '<div><h4 class="icon-head head-edit-form fieldset-legend">' . $this->getLegend() . '</h4>' . "\n";
        $html .= $this->getElementHtml();
        $html .= '</div>';
        return $html;
    }

    /**
     * Add field to fieldset
     *
     * @param string $elementId
     * @param string $type
     * @param array $config
     * @param bool $after
     * @param bool $isAdvanced
     * @return AbstractElement
     */
    public function addField($elementId, $type, $config, $after = false, $isAdvanced = false)
    {
        $element = parent::addField($elementId, $type, $config, $after);
        if ($renderer = Form::getFieldsetElementRenderer()) {
            $element->setRenderer($renderer);
        }
        $element->setAdvanced($isAdvanced);
        return $element;
    }

    /**
     * Return elements as html string
     *
     * @param AbstractElement[] $elements
     * @return string
     */
    protected function _elementsToHtml($elements)
    {
        $html = '';
        foreach ($elements as $element) {
            $html .= $element->toHtml();
        }
        return $html;
    }
}
