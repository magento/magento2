<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory;

use Magento\MediaGalleryApi\Model\Directory\IsBlacklistedInterface;

/**
 * Directories blacklisted for media gallery. This class should be used for DI configuration.
 *
 * Please use the interface in the code (for constructor injection) instead of this implementation.
 *
 * @api
 */
class IsBlacklisted implements IsBlacklistedInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * Check if the directory path can be used in the media gallery operations
     *
     * @param string $path
     * @return bool
     */
    public function execute(string $path): bool
    {
        foreach ($this->config->get('blacklist/patterns') as $pattern) {
            if (empty($pattern)) {
                continue;
            }
            preg_match($pattern, $path, $result);

            if ($result) {
                return true;
            }
        }
        return false;
    }
}
