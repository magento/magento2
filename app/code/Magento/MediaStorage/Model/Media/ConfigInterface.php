<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Model\Media;

/**
 * Media path config SPI.
 */
interface ConfigInterface
{
    /**
     * Get base relative path to store media files.
     *
     * @return string
     */
    public function getBaseMediaPath(): string;

    /**
     * Get relative path to media file.
     *
     * @param string $file
     * @return string
     */
    public function getMediaPath(string $file): string;
}
