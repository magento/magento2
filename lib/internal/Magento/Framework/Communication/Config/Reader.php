<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config;

/**
 * Communication configuration reader.
 */
class Reader implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * @var \Magento\Framework\Communication\Config\Reader\XmlReader
     */
    protected $xmlReader;

    /**
     * @var \Magento\Framework\Communication\Config\Reader\EnvReader
     */
    protected $envReader;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Communication\Config\Reader\XmlReader $xmlConfigReader
     * @param \Magento\Framework\Communication\Config\Reader\EnvReader $envConfigReader
     */
    public function __construct(
        \Magento\Framework\Communication\Config\Reader\XmlReader $xmlConfigReader,
        \Magento\Framework\Communication\Config\Reader\EnvReader $envConfigReader
    ) {
        $this->xmlReader = $xmlConfigReader;
        $this->envReader = $envConfigReader;
    }

    /**
     * Read communication configuration.
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        return array_merge(
            $this->xmlReader->read($scope),
            $this->envReader->read($scope)
        );
    }
}
