<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SearchStorefrontConfig\App\Config\Type\System;

/**
 * System configuration reader. Created this class to encapsulate the complexity of configuration data retrieval.
 *
 * All clients of this class can use its proxy to avoid instantiation when configuration is cached.
 */
class Reader
{
    /**
     * @var \Magento\Framework\App\Config\ConfigSourceInterface
     */
    private $source;

    /**
     * @var \Magento\SearchStorefrontConfig\App\Config\Processor\Fallback
     */
    private $fallback;

    /**
     * @var \Magento\Framework\App\Config\Spi\PreProcessorInterface
     */
    private $preProcessor;

    /**
     * Reader constructor.
     * @param \Magento\Framework\App\Config\ConfigSourceInterface $source
     * @param \Magento\SearchStorefrontConfig\App\Config\Processor\Fallback $fallback
     * @param \Magento\Framework\App\Config\Spi\PreProcessorInterface $preProcessor
     * @param \Magento\Framework\App\Config\Spi\PostProcessorInterface $postProcessor
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\App\Config\ConfigSourceInterface $source,
        \Magento\SearchStorefrontConfig\App\Config\Processor\Fallback $fallback,
        \Magento\Framework\App\Config\Spi\PreProcessorInterface $preProcessor,
        \Magento\Framework\App\Config\Spi\PostProcessorInterface $postProcessor
    ) {
        $this->source = $source;
        $this->fallback = $fallback;
        $this->preProcessor = $preProcessor;
    }

    /**
     * Retrieve and process system configuration data
     *
     * Processing includes configuration fallback (default, website, store) and placeholder replacement
     *
     * @return array
     */
    public function read()
    {
        return $this->fallback->process(
            $this->preProcessor->process(
                $this->source->get()
            )
        );
    }
}
