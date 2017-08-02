<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Image\Adapter;

/**
 * Class \Magento\Framework\Image\Adapter\Config
 *
 * @since 2.0.0
 */
class Config implements \Magento\Framework\Image\Adapter\ConfigInterface
{
    const XML_PATH_IMAGE_ADAPTER = 'dev/image/default_adapter';

    const XML_PATH_IMAGE_ADAPTERS = 'dev/image/adapters';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $config;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inherit}
     *
     * @return string
     * @since 2.0.0
     */
    public function getAdapterAlias()
    {
        return (string)$this->config->getValue(self::XML_PATH_IMAGE_ADAPTER);
    }

    /**
     * {@inherit}
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getAdapters()
    {
        return $this->config->getValue(self::XML_PATH_IMAGE_ADAPTERS);
    }
}
