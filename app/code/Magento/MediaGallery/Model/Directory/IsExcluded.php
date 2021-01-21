<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory;

use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Magento\MediaGalleryApi\Model\ExcludedPatternsConfigInterface;

/**
 * Check if the path is excluded for media gallery. Directory path may be blacklisted if it's reserved by the system
 */
class IsExcluded implements IsPathExcludedInterface
{
    /**
     * @var ExcludedPatternsConfigInterface
     */
    private $config;

    /**
     * @param ExcludedPatternsConfigInterface $config
     */
    public function __construct(ExcludedPatternsConfigInterface $config)
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
