<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory;

use Magento\MediaGalleryApi\Api\IsPathBlacklistedInterface;
use Magento\MediaGalleryApi\Model\BlacklistPatternsConfigInterface;

/**
 * Check if the path is blacklisted for media gallery. Directory path may be blacklisted if it's reserved by the system
 */
class IsBlacklisted implements IsPathBlacklistedInterface
{
    /**
     * @var BlacklistPatternsConfigInterface
     */
    private $config;

    /**
     * @param BlacklistPatternsConfigInterface $config
     */
    public function __construct(BlacklistPatternsConfigInterface $config)
    {
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
        foreach ($this->config->get() as $pattern) {
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
