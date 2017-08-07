<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\Config\Spi\PreProcessorInterface;

/**
 * Class PreProcessorComposite
 * @since 2.1.3
 */
class PreProcessorComposite implements PreProcessorInterface
{
    /**
     * @var PreProcessorInterface[]
     * @since 2.1.3
     */
    private $processors = [];

    /**
     * @param PreProcessorInterface[] $processors
     * @since 2.1.3
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    /**
     * @inheritdoc
     * @since 2.1.3
     */
    public function process(array $config)
    {
        /** @var PreProcessorInterface $processor */
        foreach ($this->processors as $processor) {
            $config = $processor->process($config);
        }

        return $config;
    }
}
