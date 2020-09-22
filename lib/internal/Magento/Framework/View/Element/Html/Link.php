<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Html;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\View\Element\Template\Context;

/**
 * HTML anchor element block
 *
 * @method string getLabel()
 * @method string getPath()
 * @method string getTitle()
 *
 * @api
 * @since 100.0.2
 */
class Link extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    protected $allowedAttributes = [
        'href',
        'title',
        'charset',
        'name',
        'target',
        'hreflang',
        'rel',
        'rev',
        'accesskey',
        'shape',
        'coords',
        'tabindex',
        'onfocus',
        'onblur', // %attrs
        'id',
        'class',
        'style', // %coreattrs
        'lang',
        'dir', // %i18n
        'onclick',
        'ondblclick',
        'onmousedown',
        'onmouseup',
        'onmouseover',
        'onmousemove',
        'onmouseout',
        'onkeypress',
        'onkeydown',
        'onkeyup', // %events
    ];

    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @var Random
     */
    private $random;

    /**
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
     * Prepare link attributes as serialized and formatted string
     *
     * @return string
     */
    public function getLinkAttributes()
    {
        $attributes = [];
        foreach ($this->allowedAttributes as $attribute) {
            if ($attribute === 'style' || mb_strpos($attribute, 'on') === 0) {
                continue;
            }
            $value = $this->getDataUsingMethod($attribute);
            if ($value !== null) {
                $attributes[$attribute] = $this->escapeHtml($value);
            }
        }

        if (!empty($attributes)) {
            return $this->serialize($attributes);
        }

        return '';
    }

    /**
     * Serialize attributes
     *
     * @param   array $attributes
     * @param   string $valueSeparator
     * @param   string $fieldSeparator
     * @param   string $quote
     * @return  string
     */
    public function serialize($attributes = [], $valueSeparator = '=', $fieldSeparator = ' ', $quote = '"')
    {
        $data = [];
        foreach ($attributes as $key => $value) {
            $data[] = $key . $valueSeparator . $quote . $value . $quote;
        }

        return implode($fieldSeparator, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }

        if (!$this->getDataUsingMethod('id')) {
            $this->setDataUsingMethod('id', 'id' .$this->random->getRandomString(8));
        }

        return '<li><a ' . $this->getLinkAttributes() . ' >' . $this->escapeHtml($this->getLabel()) . '</a></li>'
            .$this->renderSpecialAttributes();
    }

    /**
     * Get href URL
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl($this->getPath());
    }

    /**
     * Render attributes that require separate tags.
     *
     * @return string
     */
    private function renderSpecialAttributes(): string
    {
        $id = $this->getDataUsingMethod('id');
        if (!$id) {
            throw new \RuntimeException('ID is required to render the link');
        }

        $html = '';
        $style = $this->getDataUsingMethod('style');
        if ($style) {
            $html .= $this->secureRenderer->renderStyleAsTag($style, "#$id");
        }
        foreach ($this->allowedAttributes as $attribute) {
            if (mb_strpos($attribute, 'on') === 0 && $value = $this->getDataUsingMethod($attribute)) {
                $html .= $this->secureRenderer->renderEventListenerAsTag(
                    $attribute,
                    $value,
                    "#$id"
                );
            }
        }

        return $html;
    }
}
