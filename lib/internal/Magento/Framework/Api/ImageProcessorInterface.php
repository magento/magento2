<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\ImageContentInterface;

/**
 * Interface ImageProcessorInterface
 *
 * @api
 */
interface ImageProcessorInterface
{
    /**
     * Process base64 encoded image data and save the image file in directory path used for temporary files
     *
     * @api
     * @param ImageContentInterface $image
     * @return string Image path
     */
    public function save(ImageContentInterface $image);
}
