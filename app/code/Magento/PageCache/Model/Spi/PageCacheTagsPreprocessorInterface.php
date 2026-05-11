<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\Spi;

/**
 * Interface for page tags preprocessors
 *
 * @api
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
