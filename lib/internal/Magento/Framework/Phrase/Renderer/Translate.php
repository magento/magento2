<?php
/**
 * Translate Phrase renderer
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Phrase\Renderer;

use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\TranslateInterface;
use Psr\Log\LoggerInterface;

class Translate implements RendererInterface
{
    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $translator;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Renderer construct
     *
     * @param \Magento\Framework\TranslateInterface $translator
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        TranslateInterface $translator,
        LoggerInterface $logger
    ) {
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * Render source text
     *
     * @param [] $source
     * @param [] $arguments
     * @return string
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(array $source, array $arguments)
    {
        $text = end($source);
        /* If phrase contains escaped quotes then use translation for phrase with non-escaped quote */
        $text = str_replace('\"', '"', $text);
        $text = str_replace("\\'", "'", $text);

        try {
            $data = $this->translator->getData();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw $e;
        }

        return array_key_exists($text, $data) ? $data[$text] : end($source);
    }
}
