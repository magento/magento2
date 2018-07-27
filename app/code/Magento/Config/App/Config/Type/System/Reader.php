<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\App\Config\Type\System;

/**
 * System configuration reader. Created this class to encapsulate the complexity of configuration data retrieval.
 *
 * All clients of this class can use its proxy to avoid instantiation when configuration is cached.
 */
class Reader
{
    /**
     * Configuration source.
     *
     * @var \Magento\Framework\App\Config\ConfigSourceInterface
     */
    private $source;

    /**
     * Fallback processor.
     *
     * @var \Magento\Store\Model\Config\Processor\Fallback
     */
    private $fallback;

    /**
     * Spi preprocessor.
     *
     * @var \Magento\Framework\App\Config\Spi\PreProcessorInterface
     */
    private $preProcessor;

    /**
     * Spi postprocessor.
     *
     * @var \Magento\Framework\App\Config\Spi\PostProcessorInterface
     */
    private $postProcessor;

    /**
     * @param \Magento\Framework\App\Config\ConfigSourceInterface $source
     * @param \Magento\Store\Model\Config\Processor\Fallback $fallback
     * @param \Magento\Framework\App\Config\Spi\PreProcessorInterface $preProcessor
     * @param \Magento\Framework\App\Config\Spi\PostProcessorInterface $postProcessor
     */
    public function __construct(
        \Magento\Framework\App\Config\ConfigSourceInterface $source,
        \Magento\Store\Model\Config\Processor\Fallback $fallback,
        \Magento\Framework\App\Config\Spi\PreProcessorInterface $preProcessor,
        \Magento\Framework\App\Config\Spi\PostProcessorInterface $postProcessor
    ) {
        $this->source = $source;
        $this->fallback = $fallback;
        $this->preProcessor = $preProcessor;
        $this->postProcessor = $postProcessor;
    }

    /**
     * Retrieve and process system configuration data.
     *
     * Processing includes configuration fallback (default, website, store) and placeholder replacement.
     *
     * @return array
     */
    public function read()
    {
        return $this->postProcessor->process(
            $this->fallback->process(
                $this->preProcessor->process(
                    $this->source->get()
                )
            )
        );
    }
}
