<?php
/**
 * Translate Phrase renderer
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Phrase\Renderer;

class Translate implements \Magento\Framework\Phrase\RendererInterface
{
    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $translator;

    /**
     * Renderer construct
     *
     * @param \Magento\Framework\TranslateInterface $translator
     */
    public function __construct(\Magento\Framework\TranslateInterface $translator)
    {
        $this->translator = $translator;
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

        $data = $this->translator->getData();

        return array_key_exists($text, $data) ? $data[$text] : $text;
    }
}
