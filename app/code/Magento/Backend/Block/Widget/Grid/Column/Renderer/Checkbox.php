<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Grid checkbox column renderer
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var int
     */
    protected $_defaultWidth = 55;

    /**
     * @var array
     */
    protected $_values;

    /**
     * @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter
     */
    protected $_converter;

    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @var Random
     */
    private $random;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param Options\Converter $converter
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null,
        ?Random $random = null
    ) {
        parent::__construct($context, $data);
        $this->_converter = $converter;
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        $this->random = $random ?? ObjectManager::getInstance()->get(Random::class);
    }

    /**
     * Returns values of the column
     *
     * @return array
     */
    public function getValues()
    {
        if ($this->_values === null) {
            $this->_values = $this->getColumn()->getData('values') ? $this->getColumn()->getData('values') : [];
        }
        return $this->_values;
    }

    /**
     * Prepare data for renderer
     *
     * @return array
     */
    protected function _getValues()
    {
        $values = $this->getColumn()->getValues();
        return $this->_converter->toFlatArray($values);
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $values = $this->_getValues();
        $value = $row->getData($this->getColumn()->getIndex());
        $checked = '';
        if (is_array($values)) {
            $checked = in_array($value, $values) ? ' checked="checked"' : '';
        } else {
            $checkedValue = $this->getColumn()->getValue();
            if ($checkedValue !== null) {
                $checked = $value === $checkedValue ? ' checked="checked"' : '';
            }
        }

        $disabled = '';
        $disabledValues = $this->getColumn()->getDisabledValues();
        if (is_array($disabledValues)) {
            $disabled = in_array($value, $disabledValues) ? ' disabled="disabled"' : '';
        } else {
            $disabledValue = $this->getColumn()->getDisabledValue();
            if ($disabledValue !== null) {
                $disabled = $value === $disabledValue ? ' disabled="disabled"' : '';
            }
        }

        $this->setDisabled($disabled);

        if ($this->getNoObjectId() || $this->getColumn()->getUseIndex()) {
            $v = $value;
        } else {
            $v = $row->getId() != "" ? $row->getId() : $value;
        }

        return $this->_getCheckboxHtml($v, $checked);
    }

    /**
     * Render checkbox HTML.
     *
     * @param string $value   Value of the element
     * @param bool   $checked Whether it is checked
     * @return string
     */
    protected function _getCheckboxHtml($value, $checked)
    {
        $html = '<label class="data-grid-checkbox-cell-inner" ';
        $html .= ' for="id_' . $this->escapeHtml($value) . '">';
        $html .= '<input type="checkbox" ';
        $html .= 'name="' . $this->getColumn()->getFieldName() . '" ';
        $html .= 'value="' . $this->escapeHtml($value) . '" ';
        $html .= 'id="id_' . $this->escapeHtml($value) . '" ';
        $html .= 'class="' .
            ($this->getColumn()->getInlineCss() ? $this->getColumn()->getInlineCss() : 'checkbox') .
            ' admin__control-checkbox' . '"';
        $html .= $checked . $this->getDisabled() . '/>';
        $html .= '<label for="id_' . $this->escapeHtml($value) . '"></label>';
        $html .= '</label>';
        /* ToDo UI: add class="admin__field-label" after some refactoring _fields.less */
        return $html;
    }

    /**
     * Renders header of the column
     *
     * @return string
     */
    public function renderHeader()
    {
        if ($this->getColumn()->getHeader()) {
            return parent::renderHeader();
        }

        $checked = '';
        if ($filter = $this->getColumn()->getFilter()) {
            $checked = $filter->getValue() ? ' checked="checked"' : '';
        }

        $disabled = '';
        if ($this->getColumn()->getDisabled()) {
            $disabled = ' disabled="disabled"';
        }
        $id = 'id' .$this->random->getRandomString(10);
        $html = '<th class="data-grid-th data-grid-actions-cell"><input type="checkbox" ';
        $html .= 'id="' .$id .'" ';
        $html .= 'name="' . $this->getColumn()->getFieldName() . '" ';
        $html .= 'class="admin__control-checkbox"' . $checked . $disabled . ' ';
        $html .= 'title="' . __('Select All') . '"/><label></label></th>';
        $html .= $this->secureRenderer->renderEventListenerAsTag(
            'onclick',
            $this->getColumn()->getGrid()->getJsObjectName() . '.checkCheckboxes(this)',
            "#$id"
        );

        return $html;
    }
}
