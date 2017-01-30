<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form multiline text elements
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

class Multiline extends AbstractElement
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
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('text');
        $this->setLineCount(2);
    }

    /**
     * @return string[]
     */
    public function getHtmlAttributes()
    {
        return [
            'type',
            'title',
            'class',
            'style',
            'onclick',
            'onchange',
            'disabled',
            'maxlength',
            'data-form-part',
            'data-role',
            'data-action'
        ];
    }

    /**
     * @param int $suffix
     * @return string
     */
    public function getLabelHtml($suffix = 0)
    {
        return parent::getLabelHtml($suffix);
    }

    /**
     * Get element HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';
        $lineCount = $this->getLineCount();

        for ($i = 0; $i < $lineCount; $i++) {
            if ($i == 0 && $this->getRequired()) {
                $this->setClass('input-text admin__control-text required-entry _required');
            } else {
                $this->setClass('input-text admin__control-text');
            }
            $html .= '<div class="multi-input admin__field-control"><input id="' .
                $this->getHtmlId() .
                $i .
                '" name="' .
                $this->getName() .
                '[' .
                $i .
                ']' .
                '" value="' .
                $this->getEscapedValue(
                    $i
                ) . '" ' . $this->serialize(
                    $this->getHtmlAttributes()
                ) . '  ' . $this->_getUiId(
                    $i
                ) . '/>' . "\n";
            if ($i == 0) {
                $html .= $this->getAfterElementHtml();
            }
            $html .= '</div>';
        }
        return $html;
    }

    /**
     * @return mixed
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getDefaultHtml()
    {
        $html = '';
        $lineCount = $this->getLineCount();

        for ($i = 0; $i < $lineCount; $i++) {
            $html .= $this->getNoSpan() === true ? '' : '<span class="field-row">' . "\n";
            if ($i == 0) {
                $html .= '<label for="' .
                    $this->getHtmlId() .
                    $i .
                    '">' .
                    $this->getLabel() .
                    ($this->getRequired() ? ' <span class="required">*</span>' : '') .
                    '</label>' .
                    "\n";
                if ($this->getRequired()) {
                    $this->setClass('input-text required-entry');
                }
            } else {
                $this->setClass('input-text');
                $html .= '<label>&nbsp;</label>' . "\n";
            }
            $html .= '<input id="' .
                $this->getHtmlId() .
                $i .
                '" name="' .
                $this->getName() .
                '[' .
                $i .
                ']' .
                '" value="' .
                $this->getEscapedValue(
                    $i
                ) . '"' . $this->serialize(
                    $this->getHtmlAttributes()
                ) . ' />' . "\n";
            if ($i == 0) {
                $html .= $this->getAfterElementHtml();
            }
            $html .= $this->getNoSpan() === true ? '' : '</span>' . "\n";
        }
        return $html;
    }
}
