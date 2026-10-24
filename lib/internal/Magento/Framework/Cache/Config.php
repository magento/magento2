<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
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
     * @inheritDoc
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->_dataStorage->get('types', []);
    }

    /**
     * @inheritDoc
     *
     * @param string $type
     * @return array
     */
    public function getType($type)
    {
        return $this->_dataStorage->get('types/' . $type, []);
    }
}
