<?php
/**
 * Translate Inline Phrase renderer
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Phrase\Renderer;

class Inline implements \Magento\Framework\Phrase\RendererInterface
{
    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $translator;

    /**
     * @var \Magento\Framework\Translate\Inline\ProviderInterface
     */
    protected $inlineProvider;

    /**
     * @param \Magento\Framework\TranslateInterface $translator
     * @param \Magento\Framework\Translate\Inline\ProviderInterface $inlineProvider
     */
    public function __construct(
        \Magento\Framework\TranslateInterface $translator,
        \Magento\Framework\Translate\Inline\ProviderInterface $inlineProvider
    ) {
        $this->translator = $translator;
        $this->inlineProvider = $inlineProvider;
    }

    /**
     * Render source text
     *
     * @param [] $source
     * @param [] $arguments
     * @return string
     */
    public function render(array $source, array $arguments)
    {
        $text = end($source);

        if (!$this->inlineProvider->get()->isAllowed()) {
            return $text;
        }

        if (strpos($text, '{{{') === false
            || strpos($text, '}}}') === false
            || strpos($text, '}}{{') === false
        ) {
            $text = '{{{'
                . implode('}}{{', array_reverse($source))
                . '}}{{' . $this->translator->getTheme() . '}}}';
        }

        return $text;
    }
}
