<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory;

/**
 * Media gallery directory config
 */
interface ConfigInterface
{
    /**
     * Returns list of blacklist RegEx patterns
     *
     * @return array
     */
    public function getBlacklistPatterns(): array;
}
