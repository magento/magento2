<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Image\Adapter;

class Config implements \Magento\Framework\Image\Adapter\ConfigInterface
{
    const XML_PATH_IMAGE_ADAPTER = 'dev/image/default_adapter';

    const XML_PATH_IMAGE_ADAPTERS = 'dev/image/adapters';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inherit}
     *
     * @return string
     */
    public function getAdapterAlias()
    {
        return (string)$this->config->getValue(self::XML_PATH_IMAGE_ADAPTER);
    }

    /**
     * {@inherit}
     *
     * @return mixed
     */
    public function getAdapters()
    {
        return $this->config->getValue(self::XML_PATH_IMAGE_ADAPTERS);
    }
}
