<?php
/**
 * Cache configuration model. Provides cache configuration data to the application
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache;

class Config implements ConfigInterface
{
    /**
     * @var \Magento\Framework\Cache\Config\Data
     */
    protected $_dataStorage;

    /**
     * @param \Magento\Framework\Cache\Config\Data $dataStorage
     */
    public function __construct(\Magento\Framework\Cache\Config\Data $dataStorage)
    {
        $this->_dataStorage = $dataStorage;
    }

    /**
     * {inheritdoc}
     *
     * @return array
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
     */
    public function getType($type)
    {
        return $this->_dataStorage->get('types/' . $type, []);
    }
}
