<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\View\Helper;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRender\EventHandlerData;
use Magento\Framework\View\Helper\SecureHtmlRender\HtmlRenderer;
use Magento\Framework\View\Helper\SecureHtmlRender\SecurityProcessorInterface;
use Magento\Framework\View\Helper\SecureHtmlRender\TagData;

/**
 * Render HTML elements with consideration to application security.
 */
class SecureHtmlRenderer
{
    /**
     * @var HtmlRenderer
     */
    private $renderer;

    /**
     * @var SecurityProcessorInterface[]
     */
    private $processors;

    /**
     * @var Random
     */
    private $random;

    /**
     * @param HtmlRenderer $renderer
     * @param Random $random
     * @param SecurityProcessorInterface[] $processors
     */
    public function __construct(HtmlRenderer $renderer, Random $random, array $processors = [])
    {
        $this->renderer = $renderer;
        $this->random = $random;
        $this->processors = $processors;
    }

    /**
     * Renders HTML tag while possibly modifying or using it's attributes and content for security reasons.
     *
     * @param string $tagName Like "script" or "style"
     * @param string[] $attributes Attributes map, values must not be escaped.
     * @param string|null $content Tag's content.
     * @param bool $textContent Whether to treat the tag's content as text or HTML.
     * @return string
     */
    public function renderTag(
        string $tagName,
        array $attributes,
        ?string $content = null,
        bool $textContent = true
    ): string {
        $tag = new TagData($tagName, $attributes, $content, $textContent);
        foreach ($this->processors as $processor) {
            $tag = $processor->processTag($tag);
        }

        return $this->renderer->renderTag($tag);
    }

    /**
     * Render event listener as an HTML attribute while possibly modifying or using it's content for security reasons.
     *
     * @param string $eventName Full attribute name like "onclick".
     * @param string $javascript
     * @return string
     */
    public function renderEventListener(string $eventName, string $javascript): string
    {
        $event = new EventHandlerData($eventName, $javascript);
        foreach ($this->processors as $processor) {
            $event = $processor->processEventHandler($event);
        }

        return $this->renderer->renderEventHandler($event);
    }

    /**
     * Render event listener script as a separate tag instead of an attribute.
     *
     * @param string $eventName Full event name like "onclick".
     * @param string $attributeJavascript JS that would've gone to an HTML attribute.
     * @param string $elementSelector CSS selector for the element we handle the event for.
     * @return string Result script tag.
     */
    public function renderEventListenerAsTag(
        string $eventName,
        string $attributeJavascript,
        string $elementSelector
    ): string {
        if (!$eventName || !$attributeJavascript || !$elementSelector || mb_strpos($eventName, 'on') !== 0) {
            throw new \InvalidArgumentException('Invalid JS event handler data provided');
        }

        $random = $this->random->getRandomString(10);
        $listenerFunction = 'eventListener' .$random;
        $elementName = 'listenedElement' .$random;
        $script = <<<script
            function {$listenerFunction} () {
                {$attributeJavascript};
            }
            var {$elementName} = document.querySelector("{$elementSelector}");
            if ({$elementName}) {
                {$elementName}.{$eventName} = function (event) {
                    var targetElement = {$elementName};
                    if (event && event.target) {
                        targetElement = event.target;
                    }
                    {$listenerFunction}.apply(targetElement);
                }
            }
script;

        return $this->renderTag('script', ['type' => 'text/javascript'], $script, false);
    }

    /**
     * Render "style" attribute as a separate tag instead.
     *
     * @param string $style
     * @param string $selector Must resolve to a single node.
     * @return string
     */
    public function renderStyleAsTag(string $style, string $selector): string
    {
        $stylePairs = array_filter(array_map('trim', explode(';', $style)));
        if (!$stylePairs || !$selector) {
            throw new \InvalidArgumentException('Invalid style data given');
        }

        $elementVariable = 'elem' .$this->random->getRandomString(8);
        /** @var string[] $styles */
        $stylesAssignments = '';
        foreach ($stylePairs as $stylePair) {
            $exploded = array_map('trim', explode(':', $stylePair));
            if (count($exploded) < 2) {
                throw new \InvalidArgumentException('Invalid CSS given');
            }
            //Converting to camelCase
            $styleAttribute = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $exploded[0]))));
            if (count($exploded) > 2) {
                //For cases when ":" is encountered in the style's value.
                $exploded[1] = join('', array_slice($exploded, 1));
            }
            $styleValue = str_replace('\'', '\\\'', trim($exploded[1]));
            $stylesAssignments .= "$elementVariable.style.$styleAttribute = '$styleValue';\n";
        }

        return $this->renderTag(
            'script',
            ['type' => 'text/javascript'],
            "var $elementVariable = document.querySelector('$selector');\n"
            ."if ($elementVariable) {\n{$stylesAssignments}}",
            false
        );
    }
}
