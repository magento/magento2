<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source\Image;

class Adapter implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\Image\Adapter\ConfigInterface
     */
    protected $config;

    /**
     * @param \Magento\Framework\Image\Adapter\ConfigInterface $config
     */
    public function __construct(\Magento\Framework\Image\Adapter\ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Return hash of image adapter codes and labels
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->config->getAdapters() as $alias => $adapter) {
            $result[$alias] = __($adapter['title']);
        }

        return $result;
    }
}
