<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaContentApi\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class responsible to provide access to system configuration related to the Media Gallery
 */
class Config
{
    /**
     * Path to enable/disable media gallery in the system settings.
     */
    private const XML_PATH_ENABLED = 'system/media_gallery/enabled';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if new media gallery enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }
}
