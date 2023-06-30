<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Button widget
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 100.0.2
 */
class Button extends \Magento\Backend\Block\Widget
{
    /**
     * @var Random
     */
    private $random;

    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @param Context $context
     * @param array $data
     * @param Random|null $random
     * @param SecureHtmlRenderer|null $htmlRenderer
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?Random $random = null,
        ?SecureHtmlRenderer $htmlRenderer = null
    ) {
        parent::__construct($context, $data);
        $this->random = $random ?? ObjectManager::getInstance()->get(Random::class);
        $this->secureRenderer = $htmlRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
    }

    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('Magento_Backend::widget/button.phtml');
        parent::_construct();
    }

    /**
     * Retrieve button type
     *
     * @return string
     */
    public function getType()
    {
        if (in_array($this->getData('type'), ['reset', 'submit'])) {
            return $this->getData('type');
        }
        return 'button';
    }

    /**
     * Retrieve onclick handler
     *
     * @return null|string
     */
    public function getOnClick()
    {
        return $this->getData('on_click') ?: $this->getData('onclick');
    }

    /**
     * Retrieve attributes html
     *
     * @return string
     */
    public function getAttributesHtml()
    {
        $disabled = $this->getDisabled() ? 'disabled' : '';
        $title = $this->getTitle();
        if (!$title) {
            $title = $this->getLabel();
        }
        $classes = [];
        $classes[] = 'action-default';
        $classes[] = 'scalable';
        if ($this->getClass()) {
            $classes[] = $this->getClass();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }

        return $this->_attributesToHtml($this->_prepareAttributes($title, $classes, $disabled));
    }

    /**
     * Prepare attributes
     *
     * @param string $title
     * @param array $classes
     * @param string $disabled
     * @return array
     */
    protected function _prepareAttributes($title, $classes, $disabled)
    {
        $attributes = [
            'id' => $this->getId(),
            'name' => $this->getElementName(),
            'title' => $title,
            'type' => $this->getType(),
            'class' => join(' ', $classes),
            'value' => $this->getValue(),
            'disabled' => $disabled,
        ];
        if ($this->hasData('onclick_attribute')) {
            $attributes['onclick'] = $this->getData('onclick_attribute');
        }
        if ($this->hasData('backend_button_widget_hook_id')) {
            $attributes['backend-button-widget-hook-id'] = $this->getData('backend_button_widget_hook_id');
        }
        if ($this->getDataAttribute()) {
            foreach ($this->getDataAttribute() as $key => $attr) {
                $attributes['data-' . $key] = is_scalar($attr) ? $attr : json_encode($attr);
            }
        }
        return $attributes;
    }

    /**
     * Attributes list to html
     *
     * @param array $attributes
     * @return string
     */
    protected function _attributesToHtml($attributes)
    {
        $html = '';
        foreach ($attributes as $attributeKey => $attributeValue) {
            if ($attributeValue === null || $attributeValue == '') {
                continue;
            }
            $html .= $attributeKey . '="' . $this->escapeHtmlAttr($attributeValue, false) . '" ';
        }

        return $html;
    }

    /**
     * @inheritDoc
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $buttonId = 'buttonId' .$this->random->getRandomString(10);
        $this->setData('backend_button_widget_hook_id', $buttonId);

        $afterHtml = $this->getAfterHtml();
        if ($this->getOnClick()) {
            $afterHtml .= $this->secureRenderer->renderEventListenerAsTag(
                'onclick',
                $this->getOnClick(),
                "*[backend-button-widget-hook-id='$buttonId']"
            );
        }
        if ($this->getStyle()) {
            $afterHtml .= $this->secureRenderer->renderStyleAsTag($this->getStyle(), "#{$this->getId()}");
        }
        $this->setAfterHtml($afterHtml);

        return $this;
    }
}
