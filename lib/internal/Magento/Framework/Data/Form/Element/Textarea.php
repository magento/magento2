<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form textarea element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

/**
 * @method Textarea setExtType($extType)
 * @method mixed getCols()
 * @method Textarea setCols($cols)
 * @method mixed getRows()
 * @method mixed setRows($rows)
 */
class Textarea extends AbstractElement
{
    /**
     * default number of rows
     *
     * @var int
     */
    const DEFAULT_ROWS = 2;
    /**
     * default number of cols
     *
     * @var int
     */
    const DEFAULT_COLS = 15;

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
            'disabled',
            'onkeyup',
            'tabindex',
            'data-form-part'
        ];
    }

    /**
     * Return the element as HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $this->addClass('textarea');
        $html = '<textarea id="' . $this->getHtmlId() . '" name="' . $this->getName() . '" ' . $this->serialize(
                $this->getHtmlAttributes()
            ) . $this->_getUiId() . ' >';
        $html .= $this->getEscapedValue();
        $html .= "</textarea>";
        $html .= $this->getAfterElementHtml();
        return $html;
    }
}
