<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\Config;

use Magento\Framework\Config\CacheInterface;

/**
 * Class Data
 *
 * Config data
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Data constructor.
     *
     * @param Reader $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        Reader $reader,
        CacheInterface $cache,
        $cacheId = 'magento_report_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }
}
