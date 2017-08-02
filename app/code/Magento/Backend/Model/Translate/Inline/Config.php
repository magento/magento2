<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Translate\Inline;

/**
 * Backend Inline Translation config
 * @api
 * @since 2.0.0
 */
class Config implements \Magento\Framework\Translate\Inline\ConfigInterface
{
    /**
     * @var \Magento\Backend\App\ConfigInterface
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var \Magento\Developer\Helper\Data
     * @since 2.0.0
     */
    protected $devHelper;

    /**
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Developer\Helper\Data $devHelper
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function isActive($scope = null)
    {
        return $this->config->isSetFlag('dev/translate_inline/active_admin');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isDevAllowed($scope = null)
    {
        return $this->devHelper->isDevAllowed($scope);
    }
}
