<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\Config\Spi\PostProcessorInterface;

/**
 * @inheritdoc
 * @package Magento\Framework\App\Config
 */
class PostProcessorComposite implements PostProcessorInterface
{
    /** @var  PostProcessorInterface[] */
    private $processors;

    /**
     * @param array $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    /**
     * @param array $config
     * @return array
     */
    public function process(array $config)
    {
        foreach ($this->processors as $processor) {
            $config = $processor->process($config);
        }

        return $config;
    }
}
