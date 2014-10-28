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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App\Config;

class Initial
{
    /**
     * Cache identifier used to store initial config
     */
    const CACHE_ID = 'initial_config';

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
     * @param \Magento\Framework\App\Config\Initial\Reader $reader
     * @param \Magento\Framework\App\Cache\Type\Config $cache
     */
    public function __construct(
        \Magento\Framework\App\Config\Initial\Reader $reader,
        \Magento\Framework\App\Cache\Type\Config $cache
    ) {
        $data = $cache->load(self::CACHE_ID);
        if (!$data) {
            $data = $reader->read();
            $cache->save(serialize($data), self::CACHE_ID);
        } else {
            $data = unserialize($data);
        }
        $this->_data = $data['data'];
        $this->_metadata = $data['metadata'];
    }

    /**
     * Get initial data by given scope
     *
     * @param string $scope
     * @return array
     */
    public function getData($scope)
    {
        list($scopeType, $scopeCode) = array_pad(explode('|', $scope), 2, null);

        if (\Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT == $scopeType) {
            return isset($this->_data[$scopeType]) ? $this->_data[$scopeType] : array();
        } elseif ($scopeCode) {
            return isset($this->_data[$scopeType][$scopeCode]) ? $this->_data[$scopeType][$scopeCode] : array();
        }
        return array();
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
