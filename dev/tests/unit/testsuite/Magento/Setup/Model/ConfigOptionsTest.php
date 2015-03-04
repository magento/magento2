<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

class ConfigOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigOptions
     */
    private $object;

    protected function setUp()
    {
        $random = $this->getMock('Magento\Framework\Math\Random', [], [], '', false);
        $random->expects($this->any())->method('getRandomString')->willReturn('key');
        $loader = $this->getMock('\Magento\Framework\Module\ModuleList\Loader', [], [], '', false);
        $loader->expects($this->any())->method('load')->willReturn(['module1', 'module2']);
        $this->object = new ConfigOptions($random, $loader);
    }

    public function testGetOptions()
    {
        $options = $this->object->getOptions();
        $this->assertInstanceOf('\Magento\Framework\Setup\TextConfigOption', $options[0]);
        $this->assertInstanceOf('\Magento\Framework\Setup\MultiSelectConfigOption', $options[1]);
        $this->assertEquals(2, count($options));
    }

    public function testCreateConfig()
    {
        $config = $this->object->createConfig([ConfigOptions::INPUT_KEY_CRYPT_KEY => 'key']);
        $this->assertNotEmpty($config['install']['date']);
        $this->assertEquals('key', $config['crypt']['key']);
        $this->assertNotEmpty($config['modules']);
        $this->assertEquals(2, count($config['modules']));
    }

    /**
     * @param array $options
     * @dataProvider createConfigNoKeyDataProvider
     */
    public function testCreateConfigNoKey(array $options)
    {
        $config = $this->object->createConfig($options);
        $this->assertEquals(md5('key'), $config['crypt']['key']);
    }

    /**
     * @return array
     */
    public function createConfigNoKeyDataProvider()
    {
        return [
            'no data' => [[]],
            'no frontName' => [['something_else' => 'something']],
        ];
    }

    /**
     * @param array $options
     *
     * @dataProvider createConfigInvalidKeyDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid encryption key.
     */
    public function testCreateConfigInvalidKey(array $options)
    {
        $this->object->createConfig($options);
    }

    /**
     * @return array
     */
    public function createConfigInvalidKeyDataProvider()
    {
        return [
            [[ConfigOptions::INPUT_KEY_CRYPT_KEY => '']],
            [[ConfigOptions::INPUT_KEY_CRYPT_KEY => '0']],
        ];
    }
}
