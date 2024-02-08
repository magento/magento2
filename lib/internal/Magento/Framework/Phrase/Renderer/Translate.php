<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Phrase\Renderer;

use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\TranslateInterface;
use Psr\Log\LoggerInterface;

/**
 * Translate Phrase renderer
 */
class Translate implements RendererInterface
{
    /**
     * @var TranslateInterface
     */
    protected $translator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var MessageFormatter
     */
    private $messageFormatter;

    /**
     * Renderer construct
     *
     * @param TranslateInterface $translator
     * @param LoggerInterface $logger
     * @param MessageFormatter $messageFormatter
     */
    public function __construct(
        TranslateInterface $translator,
        LoggerInterface $logger,
        MessageFormatter $messageFormatter
    ) {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->messageFormatter = $messageFormatter;
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
        $text = strtr($text, ['\"' => '"', "\\'" => "'"]);

        try {
            $data = $this->translator->getData();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw $e;
        }

        $source[] = array_key_exists($text, $data) ? $data[$text] : end($source);

        return $this->messageFormatter->render($source, $arguments);
    }
}
