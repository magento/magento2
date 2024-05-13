<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Magento Form element renderer to display link element
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Link form element widget.
 */
class Link extends AbstractElement
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @param SecureHtmlRenderer|null $secureHtmlRenderer
     * @param Random|null $random
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = [],
        ?SecureHtmlRenderer $secureHtmlRenderer = null,
        ?Random $random = null
    ) {
        $secureHtmlRenderer = $secureHtmlRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        $random = $random ?? ObjectManager::getInstance()->get(Random::class);
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data, $secureHtmlRenderer, $random);
        $this->setType('link');
    }

    /**
     * Generates element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getBeforeElementHtml() . '<a id="' . $this->getHtmlId() . '" ' . $this->serialize(
            $this->getHtmlAttributes()
        ) . $this->_getUiId() . '>' . $this->getEscapedValue() . "</a>\n" . $this->getAfterElementHtml();
        return $html;
    }

    /**
     * Prepare array of anchor attributes
     *
     * @return string[]
     */
    public function getHtmlAttributes()
    {
        return [
            'charset',
            'coords',
            'href',
            'hreflang',
            'rel',
            'rev',
            'name',
            'shape',
            'target',
            'accesskey',
            'class',
            'dir',
            'lang',
            'style',
            'tabindex',
            'title',
            'xml:lang',
            'onblur',
            'onclick',
            'ondblclick',
            'onfocus',
            'onmousedown',
            'onmousemove',
            'onmouseout',
            'onmouseover',
            'onmouseup',
            'onkeydown',
            'onkeypress',
            'onkeyup',
            'data-role',
            'data-action'
        ];
    }
}
