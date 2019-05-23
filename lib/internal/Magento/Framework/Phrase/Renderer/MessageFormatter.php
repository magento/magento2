<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Phrase\Renderer;

use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\TranslateInterface;

/**
 * Process texts to resolve ICU MessageFormat
 */
class MessageFormatter implements RendererInterface
{
    /** @var TranslateInterface */
    private $translate;

    /**
     * @param TranslateInterface $translate
     */
    public function __construct(TranslateInterface $translate)
    {
        $this->translate = $translate;
    }

    /**
     * @inheritDoc
     */
    public function render(array $source, array $arguments)
    {
        $text = end($source);

        if (strpos($text, '{') === false) {
            // About 5x faster for non-MessageFormatted strings
            // Only slightly slower for MessageFormatted strings (~0.3x)
            return $text;
        }

        $result = \MessageFormatter::formatMessage($this->translate->getLocale(), $text, $arguments);
        return $result !== false ? $result : $text;
    }
}
