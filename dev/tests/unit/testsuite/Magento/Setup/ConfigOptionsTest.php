<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup;

class ConfigOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigOptions
     */
    private $object;

    protected function setUp()
    {
        $this->object = new ConfigOptions();
    }

    public function testCreateConfig()
    {
        $config = $this->object->createConfig([]);
        $this->assertNotEmpty($config['install']['date']);
    }
}
