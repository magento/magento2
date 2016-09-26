<?php
/**
 * Initial configuration data container. Provides interface for reading initial config values
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Json\JsonInterface;

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
    protected $_data = [];

    /**
     * Config metadata
     *
     * @var array
     */
    protected $_metadata = [];

    /**
     * @var JsonInterface
     */
    private static $json;

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
            $cache->save($this->getJson()->encode($data), self::CACHE_ID);
        } else {
            $data = $this->getJson()->decode($data);
        }
        $this->_data = $data['data'];
        $this->_metadata = $data['metadata'];
    }

    /**
     * Get initial data by given scope
     *
     * @param string $scope Format is scope type and scope code separated by pipe: e.g. "type|code"
     * @return array
     */
    public function getData($scope)
    {
        list($scopeType, $scopeCode) = array_pad(explode('|', $scope), 2, null);

        if (ScopeConfigInterface::SCOPE_TYPE_DEFAULT == $scopeType) {
            return isset($this->_data[$scopeType]) ? $this->_data[$scopeType] : [];
        } elseif ($scopeCode) {
            return isset($this->_data[$scopeType][$scopeCode]) ? $this->_data[$scopeType][$scopeCode] : [];
        }
        return [];
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

    /**
     * Get json encoder/decoder
     *
     * @return JsonInterface
     * @deprecated
     */
    private function getJson()
    {
        if (self::$json === null) {
            self::$json = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(JsonInterface::class);
        }
        return self::$json;
    }

    /**
     * Set json encoder/decoder
     *
     * @param JsonInterface $json
     * @return void
     * @deprecated
     */
    public static function setJson(JsonInterface $json)
    {
        self::$json = $json;
    }
}
