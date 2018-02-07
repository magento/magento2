<?php
/**
 * Translate Inline Phrase renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Phrase\Renderer;

use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\TranslateInterface;
use Magento\Framework\Translate\Inline\ProviderInterface;
use Psr\Log\LoggerInterface;

class Inline implements RendererInterface
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
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\TranslateInterface $translator
     * @param \Magento\Framework\Translate\Inline\ProviderInterface $inlineProvider
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        TranslateInterface $translator,
        ProviderInterface $inlineProvider,
        LoggerInterface $logger
    ) {
        $this->translator = $translator;
        $this->inlineProvider = $inlineProvider;
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

        try {
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
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw $e;
        }

        return $text;
    }
}
