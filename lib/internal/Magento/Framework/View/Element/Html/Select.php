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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Element\Html;

/**
 * HTML select element block
 */
class Select extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Get options of the element
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set options for the HTML select
     *
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * Add an option to HTML select
     *
     * @param string $value  HTML value
     * @param string $label  HTML label
     * @param array  $params HTML attributes
     * @return $this
     */
    public function addOption($value, $label, $params = array())
    {
        $this->_options[] = array('value' => $value, 'label' => $label, 'params' => $params);
        return $this;
    }

    /**
     * Set element's HTML ID
     *
     * @param string $elementId ID
     * @return $this
     */
    public function setId($elementId)
    {
        $this->setData('id', $elementId);
        return $this;
    }

    /**
     * Set element's CSS class
     *
     * @param string $class Class
     * @return $this
     */
    public function setClass($class)
    {
        $this->setData('class', $class);
        return $this;
    }

    /**
     * Set element's HTML title
     *
     * @param string $title Title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setData('title', $title);
        return $this;
    }

    /**
     * HTML ID of the element
     *
     * @return string
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * CSS class of the element
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getData('class');
    }

    /**
     * Returns HTML title of the element
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * Render HTML
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _toHtml()
    {
        if (!$this->_beforeToHtml()) {
            return '';
        }

        $html = '<select name="' .
            $this->getName() .
            '" id="' .
            $this->getId() .
            '" class="' .
            $this->getClass() .
            '" title="' .
            $this->getTitle() .
            '" ' .
            $this->getExtraParams() .
            '>';

        $values = $this->getValue();
        if (!is_array($values)) {
            $values = (array)$values;
        }

        $isArrayOption = true;
        foreach ($this->getOptions() as $key => $option) {
            if ($isArrayOption && is_array($option)) {
                $value = $option['value'];
                $label = (string)$option['label'];
                $params = !empty($option['params']) ? $option['params'] : array();
            } else {
                $value = (string)$key;
                $label = (string)$option;
                $isArrayOption = false;
                $params = array();
            }

            if (is_array($value)) {
                $html .= '<optgroup label="' . $label . '">';
                foreach ($value as $keyGroup => $optionGroup) {
                    if (!is_array($optionGroup)) {
                        $optionGroup = array('value' => $keyGroup, 'label' => $optionGroup);
                    }
                    $html .= $this->_optionToHtml($optionGroup, in_array($optionGroup['value'], $values));
                }
                $html .= '</optgroup>';
            } else {
                $html .= $this->_optionToHtml(
                    array('value' => $value, 'label' => $label, 'params' => $params),
                    in_array($value, $values)
                );
            }
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Return option HTML node
     *
     * @param array $option
     * @param boolean $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected = false)
    {
        $selectedHtml = $selected ? ' selected="selected"' : '';
        if ($this->getIsRenderToJsTemplate() === true) {
            $selectedHtml .= ' #{option_extra_attr_' . self::calcOptionHash($option['value']) . '}';
        }

        $params = '';
        if (!empty($option['params']) && is_array($option['params'])) {
            foreach ($option['params'] as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $keyMulti => $valueMulti) {
                        $params .= sprintf(' %s="%s" ', $keyMulti, $valueMulti);
                    }
                } else {
                    $params .= sprintf(' %s="%s" ', $key, $value);
                }
            }
        }

        return sprintf(
            '<option value="%s"%s %s>%s</option>',
            $this->escapeHtml($option['value']),
            $selectedHtml,
            $params,
            $this->escapeHtml($option['label'])
        );
    }

    /**
     * Alias for toHtml()
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->toHtml();
    }

    /**
     * Calculate CRC32 hash for option value
     *
     * @param string $optionValue Value of the option
     * @return string
     */
    public function calcOptionHash($optionValue)
    {
        return sprintf('%u', crc32($this->getName() . $this->getId() . $optionValue));
    }
}
