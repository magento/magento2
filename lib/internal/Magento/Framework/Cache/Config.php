<?php
/**
 * Cache configuration model. Provides cache configuration data to the application
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache;

/**
 * Class \Magento\Framework\Cache\Config
 *
 * @since 2.0.0
 */
class Config implements ConfigInterface
{
    /**
     * @var \Magento\Framework\Cache\Config\Data
     * @since 2.0.0
     */
    protected $_dataStorage;

    /**
     * @param \Magento\Framework\Cache\Config\Data $dataStorage
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Cache\Config\Data $dataStorage)
    {
        $this->_dataStorage = $dataStorage;
    }

    /**
     * {inheritdoc}
     *
     * @return array
     * @since 2.0.0
     */
    public function getTypes()
    {
        return $this->_dataStorage->get('types', []);
    }

    /**
     * {inheritdoc}
     *
     * @param string $type
     * @return array
     * @since 2.0.0
     */
    public function getType($type)
    {
        return $this->_dataStorage->get('types/' . $type, []);
    }
}
