<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronizationApi\Model;

/**
 * Get hashed value of image content.
 */
interface GetContentHashInterface
{
    /**
     * Get hashed value of image content.
     *
     * @param string $content
     * @return string
     */
    public function execute(string $content): string;
}
