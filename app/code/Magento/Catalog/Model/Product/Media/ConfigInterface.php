<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Media library image config interface
 */
namespace Magento\Catalog\Model\Product\Media;

/**
 * Interface \Magento\Catalog\Model\Product\Media\ConfigInterface
 *
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Retrieve base url for media files
     *
     * @return string
     * @since 2.0.0
     */
    public function getBaseMediaUrl();

    /**
     * Retrieve base path for media files
     *
     * @return string
     * @since 2.0.0
     */
    public function getBaseMediaPath();

    /**
     * Retrieve url for media file
     *
     * @param string $file
     * @return string
     * @since 2.0.0
     */
    public function getMediaUrl($file);

    /**
     * Retrieve file system path for media file
     *
     * @param string $file
     * @return string
     * @since 2.0.0
     */
    public function getMediaPath($file);
}
