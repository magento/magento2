<?php

/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

/**
 * Form textarea element.
 */
class Textarea extends AbstractElement
{
    /**
     * Default number of rows
     */
    public const DEFAULT_ROWS = 2;

    /**
     * Default number of columns
     */
    public const DEFAULT_COLS = 15;

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
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('textarea');
        $this->setExtType('textarea');
        if (!$this->getRows()) {
            $this->setRows(self::DEFAULT_ROWS);
        }

        if (!$this->getCols()) {
            $this->setCols(self::DEFAULT_COLS);
        }
    }

    /**
     * Return the HTML attributes
     *
     * @return string[]
     */
    public function getHtmlAttributes()
    {
        return [
            'title',
            'class',
            'style',
            'onclick',
            'onchange',
            'rows',
            'cols',
            'readonly',
            'maxlength',
            'disabled',
            'onkeyup',
            'tabindex',
            'data-form-part',
            'data-role',
            'data-action'
        ];
    }

    /**
     * Return the element as HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $this->addClass('textarea admin__control-textarea');
        $html = '<textarea id="' . $this->getHtmlId() . '" name="' . $this->getName() . '" '
            . $this->serialize($this->getHtmlAttributes()) . $this->_getUiId() . ' >';
        $html .= $this->getEscapedValue();
        $html .= "</textarea>";
        $html .= $this->getAfterElementHtml();
        return $html;
    }
}
