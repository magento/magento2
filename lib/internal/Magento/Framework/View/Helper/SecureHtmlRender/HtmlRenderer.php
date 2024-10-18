<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Helper\SecureHtmlRender;

use Magento\Framework\Escaper;

/**
 * Renders HTML based on provided data.
 */
class HtmlRenderer
{

    /**
     * List of void elements which require a self-closing tag and don't allow content
     *
     * @var array
     */
    private const VOID_ELEMENTS_MAP = [
        'area' => true,
        'base' => true,
        'br' => true,
        'col' => true,
        'command' => true,
        'embed' => true,
        'hr' => true,
        'img' => true,
        'input' => true,
        'keygen' => true,
        'link' => true,
        'meta' => true,
        'param' => true,
        'source' => true,
        'track' => true,
        'wbr' => true,
    ];

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

    /**
     * Render the tag.
     *
     * @param TagData $tagData
     * @return string
     */
    public function renderTag(TagData $tagData): string
    {
        $attributesHtmls = [];
        foreach ($tagData->getAttributes() as $attribute => $value) {
            $attributesHtmls[] = $attribute . '="' .$this->escaper->escapeHtmlAttr($value) .'"';
        }
        $content = null;
        if ($tagData->getContent() !== null) {
            $content = $tagData->isTextContent()
                ? $this->escaper->escapeHtml($tagData->getContent()) : $tagData->getContent();
        }
        $attributesHtml = '';
        if ($attributesHtmls) {
            $attributesHtml = ' ' .implode(' ', $attributesHtmls);
        }

        $html = '<' .$tagData->getTag() .$attributesHtml;
        if (isset(self::VOID_ELEMENTS_MAP[$tagData->getTag()])) {
            $html .= '/>';
        } else {
            $html .= '>' .$content .'</' .$tagData->getTag() .'>';
        }

        return $html;
    }

    /**
     * Render the handler as an HTML attribute.
     *
     * @param EventHandlerData $eventHandlerData
     * @return string
     */
    public function renderEventHandler(EventHandlerData $eventHandlerData): string
    {
        return $eventHandlerData->getEvent() .'="'
            .$this->escaper->escapeHtmlAttr($eventHandlerData->getCode()) .'"';
    }
}
