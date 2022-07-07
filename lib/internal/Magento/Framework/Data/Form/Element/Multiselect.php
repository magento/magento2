<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form select element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Multi-select form element widget.
 */
class Multiselect extends AbstractElement
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @var Random
     */
    private $random;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = [],
        ?SecureHtmlRenderer $secureRenderer = null,
        ?Random $random = null
    ) {
        $secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        $random = $random ?? ObjectManager::getInstance()->get(Random::class);
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data, $secureRenderer, $random);
        $this->setType('select');
        $this->setExtType('multiple');
        $this->setSize(10);
        $this->secureRenderer = $secureRenderer;
        $this->random = $random;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName()
    {
        $name = parent::getName();
        if (strpos($name, '[]') === false) {
            $name .= '[]';
        }
        return $name;
    }

    /**
     * Get the element as HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $this->addClass('select multiselect admin__control-multiselect');
        $html = '';
        if ($this->getCanBeEmpty()) {
            $html .= '
                <input type="hidden" id="' . $this->getHtmlId() . '_hidden" name="' . parent::getName() . '" value="" />
                ';
        }
        if (!empty($this->_data['disabled'])) {
            $html .= '<input type="hidden" name="' . parent::getName() . '_disabled" value="" />';
        }

        $html .= '<select id="' . $this->getHtmlId() . '" name="' . $this->getName() . '" ' . $this->serialize(
            $this->getHtmlAttributes()
        ) . $this->_getUiId() . ' multiple="multiple">' . "\n";

        $value = $this->getValue();
        if (!is_array($value)) {
            $value = explode(',', $value ?? '');
        }

        $values = $this->getValues();
        if ($values) {
            foreach ($values as $option) {
                if (is_array($option['value'])) {
                    $html .= '<optgroup label="' . $option['label'] . '">' . "\n";
                    foreach ($option['value'] as $groupItem) {
                        $html .= $this->_optionToHtml($groupItem, $value);
                    }
                    $html .= '</optgroup>' . "\n";
                } else {
                    $html .= $this->_optionToHtml($option, $value);
                }
            }
        }

        $html .= '</select>' . "\n";
        $html .= $this->getAfterElementHtml();

        return $html;
    }

    /**
     * Get the HTML attributes
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
            'disabled',
            'size',
            'tabindex',
            'data-form-part',
            'data-role',
            'data-action'
        ];
    }

    /**
     * Get the default HTML
     *
     * @return string
     */
    public function getDefaultHtml()
    {
        $result = $this->getNoSpan() === true ? '' : '<span class="field-row">' . "\n";
        $result .= $this->getLabelHtml();
        $result .= $this->getElementHtml();

        if ($this->getSelectAll() && $this->getDeselectAll()) {
            $random = $this->random->getRandomString(4);
            $selectAllId = 'selId' .$random;
            $deselectAllId = 'deselId' .$random;
            $result .= '<a href="#" id="' .$selectAllId .'">' .$this->getSelectAll()
                .'</a> <span class="separator">&nbsp;|&nbsp;</span>';
            $result .= '<a href="#" id="' .$deselectAllId .'">' .$this->getDeselectAll() .'</a>';

            $result .= $this->secureRenderer->renderEventListenerAsTag(
                'onclick',
                "return {$this->getJsObjectName()}.selectAll();\nreturn false;",
                "#{$selectAllId}"
            );
            $result .= $this->secureRenderer->renderEventListenerAsTag(
                'onclick',
                "return {$this->getJsObjectName()}.deselectAll();",
                "#{$deselectAllId}"
            );
        }

        $result .= $this->getNoSpan() === true ? '' : '</span>' . "\n";

        $script = '   var ' . $this->getJsObjectName() . ' = {' . "\n";
        $script .= '     selectAll: function() { ' . "\n";
        $script .= '         var sel = $("' . $this->getHtmlId() . '");' . "\n";
        $script .= '         for(var i = 0; i < sel.options.length; i ++) { ' . "\n";
        $script .= '             sel.options[i].selected = true; ' . "\n";
        $script .= '         } ' . "\n";
        $script .= '         return false; ' . "\n";
        $script .= '     },' . "\n";
        $script .= '     deselectAll: function() {' . "\n";
        $script .= '         var sel = $("' . $this->getHtmlId() . '");' . "\n";
        $script .= '         for(var i = 0; i < sel.options.length; i ++) { ' . "\n";
        $script .= '             sel.options[i].selected = false; ' . "\n";
        $script .= '         } ' . "\n";
        $script .= '         return false; ' . "\n";
        $script .= '     }' . "\n";
        $script .= '  }' . "\n";
        $result .= $this->secureRenderer->renderTag(
            'script',
            ['type' => 'text/javascript'],
            $script,
            false
        );

        return $result;
    }

    /**
     * Get the  name of the JS object
     *
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'ElementControl';
    }

    /**
     * Render an option for the select.
     *
     * @param array $option
     * @param string[] $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected)
    {
        $optionId = 'optId' .$this->random->getRandomString(8);
        $html = '<option value="' . $this->_escape($option['value']) . '" id="' . $optionId . '" ';
        $html .= isset($option['title']) ? 'title="' . $this->_escape($option['title']) . '"' : '';
        if (in_array((string)$option['value'], $selected)) {
            $html .= ' selected="selected"';
        }
        $html .= '>' . $this->_escape($option['label']) . '</option>' . "\n";
        if (!empty($option['style'])) {
            $html .= $this->secureRenderer->renderStyleAsTag($option['style'], "#$optionId");
        }

        return $html;
    }
}
