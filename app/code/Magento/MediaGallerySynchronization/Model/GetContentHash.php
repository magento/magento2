<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Model;

use Magento\MediaGallerySynchronizationApi\Model\GetContentHashInterface;

/**
 * Get hashed value of image content.
 */
class GetContentHash implements GetContentHashInterface
{
    /**
     * Return the hash value of the given filepath.
     *
     * @param string $content
     * @return string
     */
    public function execute(string $content): string
    {
        return sha1($content);
    }
}
