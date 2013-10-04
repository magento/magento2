<?php
/**
 * Initial configuration data container. Provides interface for reading initial config values
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Config;

class Initial
{
    /**
     * Config data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Config metadata
     *
     * @var array
     */
    protected $_metadata = array();

    /**
     * @param \Magento\Core\Model\Config\Initial\Reader $reader
     * @param \Magento\Core\Model\Cache\Type\Config $cache
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Core\Model\Config\Initial\Reader $reader,
        \Magento\Core\Model\Cache\Type\Config $cache,
        $cacheId = 'initial_config'
    ) {
        $data = $cache->load($cacheId);
        if (!$data) {
            $data = $reader->read();
            $cache->save(serialize($data), $cacheId);
        } else {
            $data = unserialize($data);
        }
        $this->_data = $data['data'];
        $this->_metadata = $data['metadata'];
    }

    /**
     * Get default config
     *
     * @return array
     */
    public function getDefault()
    {
        return $this->_data['default'];
    }

    /**
     * Retrieve store initial config by code
     *
     * @param string $code
     * @return array
     */
    public function getStore($code)
    {
        return isset($this->_data['stores'][$code]) ? $this->_data['stores'][$code] : array();
    }

    /**
     * Retrieve website initial config by code
     *
     * @param string $code
     * @return array
     */
    public function getWebsite($code)
    {
        return isset($this->_data['websites'][$code]) ? $this->_data['websites'][$code] : array();
    }

    /**
     * Get configuration metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->_metadata;
    }
}
