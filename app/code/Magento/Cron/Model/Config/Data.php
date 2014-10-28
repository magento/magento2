<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Prepare cron jobs data
 */
namespace Magento\Cron\Model\Config;

class Data extends \Magento\Framework\Config\Data
{
    /**
     * Initialize parameters
     *
     * @param \Magento\Cron\Model\Config\Reader\Xml $reader
     * @param \Magento\Framework\Config\CacheInterface        $cache
     * @param \Magento\Cron\Model\Config\Reader\Db  $dbReader
     * @param string                               $cacheId
     */
    public function __construct(
        \Magento\Cron\Model\Config\Reader\Xml $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Cron\Model\Config\Reader\Db $dbReader,
        $cacheId = 'crontab_config_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
        $this->merge($dbReader->get());
    }

    /**
     * Merge cron jobs and return
     *
     * @return array
     */
    public function getJobs()
    {
        return $this->get();
    }
}
