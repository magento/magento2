<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\FilterManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filter\FilterManager\Config
     */
    protected $_config;

    protected function setUp()
    {
        $this->_config = new \Magento\Framework\Filter\FilterManager\Config(['test' => 'test']);
    }

    public function testGetFactories()
    {
        $expectedConfig = [
            'test' => 'test',
            'Magento\Framework\Filter\Factory',
            'Magento\Framework\Filter\ZendFactory',
        ];
        $this->assertEquals($expectedConfig, $this->_config->getFactories());
    }
}
