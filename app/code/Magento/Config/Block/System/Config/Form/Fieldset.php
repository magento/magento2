<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Config form fieldset renderer
 */
namespace Magento\Config\Block\System\Config\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * @api
 * @since 2.0.0
 */
class Fieldset extends \Magento\Backend\Block\AbstractBlock implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     * @since 2.0.0
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\View\Helper\Js
     * @since 2.0.0
     */
    protected $_jsHelper;

    /**
     * Whether is collapsed by default
     *
     * @var bool
     * @since 2.0.0
     */
    protected $isCollapsedDefault = false;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        array $data = []
    ) {
        $this->_jsHelper = $jsHelper;
        $this->_authSession = $authSession;
        parent::__construct($context, $data);
    }

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        $header = $this->_getHeaderHtml($element);

        $elements = $this->_getChildrenElementsHtml($element);

        $footer = $this->_getFooterHtml($element);

        return $header . $elements . $footer;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @since 2.1.0
     */
    protected function _getChildrenElementsHtml(AbstractElement $element)
    {
        $elements = '';
        foreach ($element->getElements() as $field) {
            if ($field instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
                $elements .= '<tr id="row_' . $field->getHtmlId() . '">'
                    . '<td colspan="4">' . $field->toHtml() . '</td></tr>';
            } else {
                $elements .= $field->toHtml();
            }
        }

        return $elements;
    }

    /**
     * Return header html for fieldset
     *
     * @param AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    protected function _getHeaderHtml($element)
    {
        if ($element->getIsNested()) {
            $html = '<tr class="nested"><td colspan="4"><div class="' . $this->_getFrontendClass($element) . '">';
        } else {
            $html = '<div class="' . $this->_getFrontendClass($element) . '">';
        }

        $html .= '<div class="entry-edit-head admin__collapsible-block">' .
            '<span id="' .
            $element->getHtmlId() .
            '-link" class="entry-edit-head-link"></span>';

        $html .= $this->_getHeaderTitleHtml($element);

        $html .= '</div>';
        $html .= '<input id="' .
            $element->getHtmlId() .
            '-state" name="config_state[' .
            $element->getId() .
            ']" type="hidden" value="' .
            (int)$this->_isCollapseState(
                $element
            ) . '" />';
        $html .= '<fieldset class="' . $this->_getFieldsetCss() . '" id="' . $element->getHtmlId() . '">';
        $html .= '<legend>' . $element->getLegend() . '</legend>';

        $html .= $this->_getHeaderCommentHtml($element);

        // field label column
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if ($this->getRequest()->getParam('website') || $this->getRequest()->getParam('store')) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';

        return $html;
    }

    /**
     * Get frontend class
     *
     * @param AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    protected function _getFrontendClass($element)
    {
        $group = $element->getGroup();
        $cssClass = isset($group['fieldset_css']) ? $group['fieldset_css'] : '';
        return 'section-config' . (empty($cssClass) ? '' : ' ' . $cssClass);
    }

    /**
     * Return header title part of html for fieldset
     *
     * @param AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    protected function _getHeaderTitleHtml($element)
    {
        return '<a id="' .
            $element->getHtmlId() .
            '-head" href="#' .
            $element->getHtmlId() .
            '-link" onclick="Fieldset.toggleCollapse(\'' .
            $element->getHtmlId() .
            '\', \'' .
            $this->getUrl(
                '*/*/state'
            ) . '\'); return false;">' . $element->getLegend() . '</a>';
    }

    /**
     * Return header comment part of html for fieldset
     *
     * @param AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    protected function _getHeaderCommentHtml($element)
    {
        return $element->getComment() ? '<div class="comment">' . $element->getComment() . '</div>' : '';
    }

    /**
     * Return full css class name for form fieldset
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getFieldsetCss()
    {
        /** @var \Magento\Config\Model\Config\Structure\Element\Group $group */
        $group = $this->getGroup();
        $configCss = $group->getFieldsetCss();
        return 'config admin__collapsible-block' . ($configCss ? ' ' . $configCss : '');
    }

    /**
     * Return footer html for fieldset
     * Add extra tooltip comments to elements
     *
     * @param AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    protected function _getFooterHtml($element)
    {
        $html = '</tbody></table>';
        foreach ($element->getElements() as $field) {
            if ($field->getTooltip()) {
                $html .= sprintf(
                    '<div id="row_%s_comment" class="system-tooltip-box" style="display:none;">%s</div>',
                    $field->getId(),
                    $field->getTooltip()
                );
            }
        }
        $html .= '</fieldset>' . $this->_getExtraJs($element);

        if ($element->getIsNested()) {
            $html .= '</td></tr>';
        } else {
            $html .= '</div>';
        }
        return $html;
    }

    /**
     * Return js code for fieldset:
     * - observe fieldset rows;
     * - apply collapse;
     *
     * @param AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    protected function _getExtraJs($element)
    {
        $htmlId = $element->getHtmlId();
        $output = "require(['prototype'], function(){Fieldset.applyCollapse('{$htmlId}');});";
        return $this->_jsHelper->getScript($output);
    }

    /**
     * Collapsed or expanded fieldset when page loaded?
     *
     * @param AbstractElement $element
     * @return bool
     * @since 2.0.0
     */
    protected function _isCollapseState($element)
    {
        if ($element->getExpanded() ||
            ($element->getForm() && $element->getForm()->getElements()->count() === 1)
        ) {
            return true;
        }

        $extra = $this->_authSession->getUser()->getExtra();
        if (isset($extra['configState'][$element->getId()])) {
            return $extra['configState'][$element->getId()];
        }
        return $this->isCollapsedDefault;
    }
}
