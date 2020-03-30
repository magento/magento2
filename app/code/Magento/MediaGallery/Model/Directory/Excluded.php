<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory;

use Magento\MediaGalleryApi\Model\Directory\ExcludedInterface;

/**
 * Directory paths that should not be included in the media gallery
 */
class Excluded implements ExcludedInterface
{
    /**
     * @var array
     */
    private $patterns;

    /**
     * @param array $patterns
     */
    public function __construct(
        array $patterns
    ) {
        $this->patterns = $patterns;
    }

    /**
     * Check if the path is excluded from displaying in the media gallery
     *
     * @param string $path
     * @return bool
     */
    public function isExcluded(string $path): bool
    {
        foreach ($this->patterns as $pattern) {
            preg_match($pattern, $path, $result);

            if ($result) {
                return true;
            }
        }
        return false;
    }
}
