<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\Spi;

/**
 * Interface for page tags preprocessors
 */
interface PageCacheTagsPreprocessorInterface
{
    /**
     * Change page tags and returned the modified tags
     *
     * @param array $tags
     * @return array
     */
    public function process(array $tags): array;
}
