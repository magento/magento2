<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Image include policy into sitemap file
 *
 */
namespace Magento\Sitemap\Model\Source\Product\Image;

class IncludeImage implements \Magento\Framework\Option\ArrayInterface
{
    /**#@+
     * Add Images into Sitemap possible values
     */
    const INCLUDE_NONE = 'none';

    const INCLUDE_BASE = 'base';

    const INCLUDE_ALL = 'all';

    /**#@-*/

    /**
     * Retrieve options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::INCLUDE_NONE => __('None'),
            self::INCLUDE_BASE => __('Base Only'),
            self::INCLUDE_ALL => __('All')
        ];
    }
}
