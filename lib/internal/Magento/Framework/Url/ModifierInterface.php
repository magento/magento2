<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * URL modifier interface.
 */
interface ModifierInterface
{
    /**#@+
     * Possible modes.
     */
    const MODE_ENTIRE = 'entire';
    const MODE_BASE = 'base';
    /**#@-*/

    /**
     * Modifies URL.
     *
     * @param string $url
     * @param array|null $params
     * @param string $mode
     * @return string
     */
    public function execute($url, $params = null, $mode = self::MODE_ENTIRE);
}
