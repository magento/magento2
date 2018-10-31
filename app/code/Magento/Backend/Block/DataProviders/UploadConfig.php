<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Block\DataProviders;

use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Provides additional data for image uploader
 */
class UploadConfig implements ArgumentInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Get image resize configuration
     *
     * @return int
     */
    public function getIsResizeEnabled(): int
    {
        return (int)$this->config->getValue('system/upload_configuration/enable_resize');
    }
}
