<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Html;

/**
 * HTML anchor element block
 *
 * @method string getLabel()
 * @method string getPath()
 * @method string getTitle()
 */
class Link extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    protected $allowedAttributes = [
        'href',
        'title',
        'charset',
        'name',
        'hreflang',
        'rel',
        'rev',
        'accesskey',
        'shape',
        'coords',
        'tabindex',
        'onfocus',
        'onblur', // %attrs
        'id',
        'class',
        'style', // %coreattrs
        'lang',
        'dir', // %i18n
        'onclick',
        'ondblclick',
        'onmousedown',
        'onmouseup',
        'onmouseover',
        'onmousemove',
        'onmouseout',
        'onkeypress',
        'onkeydown',
        'onkeyup', // %events
    ];

    /**
     * Prepare link attributes as serialized and formatted string
     *
     * @return string
     */
    public function getLinkAttributes()
    {
        $attributes = [];
        foreach ($this->allowedAttributes as $attribute) {
            $value = $this->getDataUsingMethod($attribute);
            if (!is_null($value)) {
                $attributes[$attribute] = $this->escapeHtml($value);
            }
        }

        if (!empty($attributes)) {
            return $this->serialize($attributes);
        }

        return '';
    }

    /**
     * Serialize attributes
     *
     * @param   array $attributes
     * @param   string $valueSeparator
     * @param   string $fieldSeparator
     * @param   string $quote
     * @return  string
     */
    public function serialize($attributes = [], $valueSeparator = '=', $fieldSeparator = ' ', $quote = '"')
    {
        $data = [];
        foreach ($attributes as $key => $value) {
            $data[] = $key . $valueSeparator . $quote . $value . $quote;
        }

        return implode($fieldSeparator, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }

        return '<li><a ' . $this->getLinkAttributes() . ' >' . $this->escapeHtml($this->getLabel()) . '</a></li>';
    }

    /**
     * Get href URL
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl($this->getPath());
    }
}
