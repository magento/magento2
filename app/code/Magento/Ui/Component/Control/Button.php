<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Control;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\UiComponent\Control\ControlInterface;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\View\Element\Template\Context;

/**
 * Widget for standard button.
 */
class Button extends Template implements ControlInterface
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
        $this->setTemplate($this->getTemplatePath());

        parent::_construct();
    }

    /**
     * Retrieve template path
     *
     * @return string
     */
    protected function getTemplatePath()
    {
        return 'Magento_Ui::control/button/default.phtml';
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
     * Retrieve attributes html
     *
     * @return string
     */
    public function getAttributesHtml()
    {
        $disabled = $this->getDisabled() ? 'disabled' : '';
        $title = $this->getTitle();
        if (empty($title)) {
            $title = $this->getLabel();
        }
        $classes = ['action-', 'scalable'];
        if ($this->hasData('class')) {
            $classes[] = $this->getClass();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }

        return $this->attributesToHtml($this->prepareAttributes($title, $classes, $disabled));
    }

    /**
     * Retrieve onclick handler
     *
     * @return string|null
     */
    public function getOnClick()
    {
        if ($this->hasData('on_click')) {
            return $this->getData('on_click');
        } else {
            $url = $this->hasData('url') ? $this->getData('url') : $this->getUrl();
            if (!empty($url)) {
                return sprintf("location.href = '%s';", $url);
            }

            return null;
        }
    }

    /**
     * @inheritDoc
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $this->setData('ui_button_widget_hook_id', 'buttonId' .$this->random->getRandomString(10));

        return $this;
    }

    /**
     * Prepare attributes
     *
     * @param string $title
     * @param array $classes
     * @param string $disabled
     * @return array
     */
    protected function prepareAttributes($title, $classes, $disabled)
    {
        $attributes = [
            'id' => $this->getId(),
            'name' => $this->getElementName(),
            'title' => $title,
            'type' => $this->getType(),
            'class' => implode(' ', $classes),
            'value' => $this->getValue(),
            'disabled' => $disabled,
        ];
        if ($this->getDataAttribute()) {
            foreach ($this->getDataAttribute() as $key => $attr) {
                $attributes['data-' . $key] = is_scalar($attr) ? $attr : json_encode($attr);
            }
        }
        if ($this->hasData('ui_button_widget_hook_id')) {
            $attributes['ui-button-widget-hook-id'] = $this->getData('ui_button_widget_hook_id');
        }

        return $attributes;
    }

    /**
     * Attributes list to html
     *
     * @param array $attributes
     * @return string
     */
    protected function attributesToHtml($attributes)
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
     * Return HTML to be rendered after the button.
     *
     * @return string|null
     */
    public function getAfterHtml(): ?string
    {
        $afterHtml = $this->getData('after_html');
        $buttonId = $this->getData('ui_button_widget_hook_id');
        if ($handler = $this->getOnClick()) {
            $afterHtml .= $this->secureRenderer->renderEventListenerAsTag(
                'onclick',
                $handler,
                "*[ui-button-widget-hook-id='$buttonId']"
            );
        }
        if ($this->getStyle()) {
            $selector = "*[ui-button-widget-hook-id='$buttonId']";
            if ($this->getId()) {
                $selector = "#{$this->getId()}";
            }
            $afterHtml .= $this->secureRenderer->renderStyleAsTag($this->getStyle(), $selector);
        }

        return $afterHtml;
    }
}
