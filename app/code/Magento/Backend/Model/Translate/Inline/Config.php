<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Translate\Inline;

/**
 * Backend Inline Translation config
 */
class Config implements \Magento\Framework\Translate\Inline\ConfigInterface
{
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Config\Helper\Data
     */
    protected $configHelper;

    /**
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Framework\App\Config\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\ConfigInterface $config,
        \Magento\Framework\App\Config\Helper\Data $helper
    ) {
        $this->config = $config;
        $this->configHelper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive($scope = null)
    {
        return $this->config->isSetFlag('dev/translate_inline/active_admin');
    }

    /**
     * {@inheritdoc}
     */
    public function isDevAllowed($scope = null)
    {
        return $this->configHelper->isDevAllowed($scope);
    }
}
