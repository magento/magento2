<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\Config\Spi\PreProcessorInterface;

/**
 * Class PreProcessorComposite
 */
class PreProcessorComposite implements PreProcessorInterface
{
    /**
     * @var PreProcessorInterface[]
     */
    private $processors = [];

    /**
     * @param PreProcessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    /**
     * @inheritdoc
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
