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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\App\DeploymentConfig;

class CacheConfig extends AbstractSegment
{
    /**
     * Array key for cache frontend
     */
    const KEY_FRONTEND = 'frontend';

    /**
     * Array key for cache type
     */
    const KEY_TYPE = 'type';

    /**
     * Segment key
     */
    const CONFIG_KEY = 'cache';

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->validate($data);
        parent::__construct($data);
    }

    /**
     * Validate data
     *
     * @param array $data
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validate(array $data)
    {
        if (!isset($data[self::KEY_FRONTEND])) {
            throw new \InvalidArgumentException('No cache frontend configuration provided.');
        }
        if (!is_array($data[self::KEY_FRONTEND])) {
            throw new \InvalidArgumentException('Invalid cache frontend configuration provided.');
        }
        foreach ($data[self::KEY_FRONTEND] as $settings) {
            if (!is_array($settings)) {
                throw new \InvalidArgumentException('Invalid cache settings.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return self::CONFIG_KEY;
    }

    /**
     * Retrieve settings for all cache front-ends configured in the system
     *
     * @return array Format: array('<frontend_id>' => array(<cache_settings>), ...)
     */
    public function getCacheFrontendSettings()
    {
        return isset($this->data[self::KEY_FRONTEND]) ? $this->data[self::KEY_FRONTEND] : array();
    }

    /**
     * Retrieve identifier of a cache frontend, configured to be used for a cache type
     *
     * @param string $cacheType Cache type identifier
     * @return string|null
     */
    public function getCacheTypeFrontendId($cacheType)
    {
        return isset($this->data[self::KEY_TYPE][$cacheType][self::KEY_FRONTEND]) ?
            $this->data[self::KEY_TYPE][$cacheType][self::KEY_FRONTEND] : null;
    }
}
