<?php

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
            // Definitely nothing to process with MessageFormatter
            return $text;
        }

        $result = \MessageFormatter::formatMessage($this->translate->getLocale(), $text, $arguments);

        // Return $text if MessageFormatter fails (for backwards-compatibility)
        return $result !== false ? $result : $text;
    }
}
