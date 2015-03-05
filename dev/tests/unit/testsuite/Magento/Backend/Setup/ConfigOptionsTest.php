<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Setup;

use Magento\Framework\Config\File\ConfigFilePool;

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

    public function testGetOptions()
    {
        $options = $this->object->getOptions();
        $this->assertInternalType('array', $options);
        foreach ($options as $option) {
            $this->assertInstanceOf('\Magento\Framework\Setup\Option\AbstractConfigOption', $option);
        }
    }

    public function testCreateConfig()
    {
        $options = [ConfigOptions::INPUT_KEY_BACKEND_FRONTNAME => 'admin'];
        $actualConfig = $this->object->createConfig($options);
        $expectedData = [
            ['file' => ConfigFilePool::APP_CONFIG, 'segment' => 'backend', 'data' => ['frontName' => 'admin']]
        ];
        $this->assertInternalType('array', $actualConfig);
        /** @var \Magento\Framework\Config\Data\ConfigData $config */
        foreach ($actualConfig as $i => $config) {
            $this->assertInstanceOf('\Magento\Framework\Config\Data\ConfigData', $config);
            $this->assertSame($expectedData[$i]['file'], $config->getFileKey());
            $this->assertSame($expectedData[$i]['segment'], $config->getSegmentKey());
            $this->assertSame($expectedData[$i]['data'], $config->getData());
        }
    }

    /**
     * @param array $options
     *
     * @dataProvider createConfigNoFrontnameDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No backend frontname provided
     */
    public function testCreateConfigNoFrontname(array $options)
    {
        $this->object->createConfig($options);
    }

    /**
     * @return array
     */
    public function createConfigNoFrontnameDataProvider()
    {
        return [
            'no data' => [[]],
            'no frontName' => [['something_else' => 'something']],
            'empty frontName' => [[ConfigOptions::INPUT_KEY_BACKEND_FRONTNAME => '']],
        ];
    }

    /**
     * @param array $options
     *
     * @dataProvider createConfigInvalidFrontnameDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid backend frontname
     */
    public function testCreateConfigInvalidFrontname(array $options)
    {
        $this->object->createConfig($options);
    }

    /**
     * @return array
     */
    public function createConfigInvalidFrontnameDataProvider()
    {
        return [
            [[ConfigOptions::INPUT_KEY_BACKEND_FRONTNAME => '**']],
            [[ConfigOptions::INPUT_KEY_BACKEND_FRONTNAME => 'invalid frontname']],
        ];
    }
}
