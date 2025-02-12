<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Radio buttons collection
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Radio buttons form element widget.
 */
class Radios extends AbstractElement
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->secureRenderer
            = $secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data, $secureRenderer);
        $this->setType('radios');
    }

    /**
     * @inheritDoc
     */
    public function getElementHtml()
    {
        $html = '';
        $value = $this->getValue();
        if ($values = $this->getValues()) {
            foreach ($values as $option) {
                $html .= $this->_optionToHtml($option, $value);
            }
        }
        $html .= $this->getAfterElementHtml();
        return $html;
    }

    /**
     * Render choices.
     *
     * @param array $option
     * @param string[] $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected)
    {
        $html = '<div class="admin__field admin__field-option">' .
            '<input type="radio"' . $this->getRadioButtonAttributes($option);
        if (is_array($option)) {
            $option = new DataObject($option);
            $optionId = $this->getHtmlId() . $option['value'];
            $html .= 'value="' . $this->_escape(
                $option['value']
            ) . '" class="admin__control-radio" id="' .$optionId  .'"';
            if ($option['value'] == $selected) {
                $html .= ' checked="checked"';
            }
            $html .= ' />';
            $html .= '<label class="admin__field-label" for="' .
                $this->getHtmlId() .
                $option['value'] .
                '"><span>' .
                $option['label'] .
                '</span></label>';
        } elseif ($option instanceof DataObject) {
            $optionId = $this->getHtmlId() . $option->getValue();
            $html .= 'id="' .$optionId  .'"' .$option->serialize(
                ['label', 'title', 'value', 'class']
            );
            if (in_array($option->getValue(), $selected)) {
                $html .= ' checked="checked"';
            }
            $html .= ' />';
            $html .= '<label class="inline" for="' .
                $this->getHtmlId() .
                $option->getValue() .
                '">' .
                $option->getLabel() .
                '</label>';
        }

        if ($option->getStyle()) {
            $html .= $this->secureRenderer->renderStyleAsTag($option->getStyle(), "#$optionId");
        }
        if ($option->getOnclick()) {
            $this->secureRenderer->renderEventListenerAsTag('onclick', $option->getOnclick(), "#$optionId");
        }
        if ($option->getOnchange()) {
            $this->secureRenderer->renderEventListenerAsTag('onchange', $option->getOnchange(), "#$optionId");
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @inheritDoc
     */
    public function getHtmlAttributes()
    {
        return array_merge(parent::getHtmlAttributes(), ['name']);
    }

    /**
     * Get a choice's HTML attributes.
     *
     * @param array $option
     * @return string
     */
    protected function getRadioButtonAttributes($option)
    {
        $html = '';
        foreach ($this->getHtmlAttributes() as $attribute) {
            if ($value = $this->getDataUsingMethod($attribute, $option['value'])) {
                $html .= ' ' . $attribute . '="' . $value . '" ';
            }
        }
        return $html;
    }
}
