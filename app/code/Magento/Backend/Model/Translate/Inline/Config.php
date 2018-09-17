<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var \Magento\Developer\Helper\Data
     */
    protected $devHelper;

    /**
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Developer\Helper\Data $devHelper
     */
    public function __construct(
        \Magento\Backend\App\ConfigInterface $config,
        \Magento\Developer\Helper\Data $devHelper
    ) {
        $this->config = $config;
        $this->devHelper = $devHelper;
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
        return $this->devHelper->isDevAllowed($scope);
    }
}
