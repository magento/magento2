<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Integration\Model\Integration;
use Magento\Backend\Block\Context;

/**
 * Render HTML <button> tag.
 *
 */
class Button extends AbstractRenderer
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
     * Button constructor.
     * @param Context $context
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null,
        ?Random $random = null
    ) {
        parent::__construct($context, $data);
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        $this->random = $random ?? ObjectManager::getInstance()->get(Random::class);
    }

    /**
     * @inheritDoc
     */
    public function render(DataObject $row)
    {
        $attributes = $this->extractAttributes($row);
        $attributes['button-renderer-hook-id'] = 'hook' .$this->random->getRandomString(10);

        return sprintf('<button %s>%s</button>', $this->renderAttributes($attributes), $this->_getValue($row))
            .$this->renderSpecialAttributes($attributes);
    }

    /**
     * Extract attributes to render.
     *
     * @param DataObject $row
     * @return string[]
     */
    private function extractAttributes(DataObject $row): array
    {
        $attributes = [];
        foreach ($this->_getValidAttributes() as $attributeName) {
            $methodName = sprintf('_get%sAttribute', ucfirst($attributeName));
            $rowMethodName = sprintf('get%s', ucfirst($attributeName));
            $attributeValue = method_exists(
                $this,
                $methodName
            ) ? $this->{$methodName}(
                $row
            ) : $this->getColumn()->{$rowMethodName}();
            if ($attributeValue) {
                $attributes[$attributeName] = $attributeValue;
            }
        }

        return $attributes;
    }

    /**
     * Determine whether current integration came from config file
     *
     * @param DataObject $row
     * @return bool
     */
    protected function _isConfigBasedIntegration(DataObject $row)
    {
        return $row->hasData(
            Integration::SETUP_TYPE
        ) && $row->getData(
            Integration::SETUP_TYPE
        ) == Integration::TYPE_CONFIG;
    }

    /**
     * Whether current item is disabled.
     *
     * @param DataObject $row
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isDisabled(DataObject $row)
    {
        return false;
    }

    /**
     * Retrieve "disabled" attribute value for the row.
     *
     * @param DataObject $row
     * @return string
     */
    protected function _getDisabledAttribute(DataObject $row)
    {
        return $this->_isDisabled($row) ? 'disabled' : '';
    }

    /**
     * Prepare attribute list. Values for attributes gathered from two sources:
     * - If getter method exists in the class - it is taken from there (getter method for "title"
     *   attribute will be "_getTitleAttribute", for "onmouseup" - "_getOnmouseupAttribute" and so on.)
     * - Then it tries to get it from the button's column layout description.
     * If received attribute value is empty - attribute is not added to final HTML.
     *
     * @param DataObject $row
     * @return array
     */
    protected function _prepareAttributes(DataObject $row)
    {
        $attributes = $this->extractAttributes($row);
        foreach ($attributes as $attributeName => $attributeValue) {
            if ($attributeName === 'style' || mb_strpos($attributeName, 'on') === 0) {
                //Will render event handlers and style as separate tags
                continue;
            }
            $attributes[] = sprintf(
                '%s="%s"',
                $attributeName,
                $this->escapeHtmlAttr($attributeValue, false)
            );
        }

        return $attributes;
    }

    /**
     * Render HTML attributes.
     *
     * @param array $attributes
     * @return string
     */
    private function renderAttributes(array $attributes): string
    {
        $html = '';
        foreach ($attributes as $attributeName => $attributeValue) {
            if ($attributeName === 'style' || mb_strpos($attributeName, 'on') === 0) {
                //Will render event handlers and style as separate tags
                continue;
            }
            $html .= ($html ? ' ' : '') ."{$attributeName}=\"{$this->escapeHtmlAttr($attributeValue)}\"";
        }

        return $html;
    }

    /**
     * Get list of available HTML attributes for this element.
     *
     * @return array
     */
    protected function _getValidAttributes()
    {
        /*
         * HTML global attributes - 'accesskey', 'class', 'id', 'lang', 'style', 'tabindex', 'title'
         * HTML mouse event attributes - 'onclick', 'ondblclick', 'onmousedown', 'onmousemove', 'onmouseout',
         *                               'onmouseover', 'onmouseup'
         * Element attributes - 'disabled', 'name', 'type', 'value'
         */
        return [
            'accesskey',
            'class',
            'id',
            'lang',
            'style',
            'tabindex',
            'title',
            'onclick',
            'ondblclick',
            'onmousedown',
            'onmousemove',
            'onmouseout',
            'onmouseover',
            'onmouseup',
            'disabled',
            'name',
            'type',
            'value'
        ];
    }

    /**
     * Get list of attributes rendered as a string (ready to be inserted into tag).
     *
     * @param array $attributes Array of attributes
     * @return string
     */
    protected function _getAttributesStr($attributes)
    {
        return join(' ', $attributes);
    }

    /**
     * Render special attributes as separate tags.
     *
     * @param string[] $attributes
     * @return string
     */
    private function renderSpecialAttributes(array $attributes): string
    {
        if (!$hookId = $attributes['button-renderer-hook-id']) {
            return '';
        }

        $html = '';
        if (!empty($attributes['style'])) {
            $html .= $this->secureRenderer->renderStyleAsTag(
                $attributes['style'],
                "[button-renderer-hook-id='$hookId']"
            );
        }
        foreach ($this->_getValidAttributes() as $attr) {
            if (!empty($attributes[$attr]) && mb_strpos($attr, 'on') === 0) {
                $html .= $this->secureRenderer->renderEventListenerAsTag(
                    $attr,
                    $attributes[$attr],
                    "*[button-renderer-hook-id='$hookId']"
                );
            }
        }

        return $html;
    }
}
