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
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[0]);
        $this->assertInstanceOf('Magento\Framework\Setup\Option\MultiSelectConfigOption', $options[1]);
        $this->assertInstanceOf('Magento\Framework\Setup\Option\SelectConfigOption', $options[2]);
        $this->assertEquals(3, count($options));
    }

    public function testCreateConfig()
    {
        $config = $this->object->createConfig([
            ConfigOptions::INPUT_KEY_CRYPT_KEY => 'key',
            ConfigOptions::INPUT_KEY_SESSION_SAVE => 'db'
        ]);
        $this->assertEquals(4, count($config));
        $this->assertNotEmpty($config['install']);
        $this->assertNotEmpty($config['crypt']);
        $this->assertEquals('key', $config['crypt']->getData()['key']);
        $this->assertNotEmpty($config['modules']);
        $this->assertEquals(2, count($config['modules']->getData()));
        $this->assertNotEmpty($config['session']);
        $this->assertEquals('db', $config['session']->getData()['save']);
    }

    public function testCreateConfigNoSessionSave()
    {
        $config = $this->object->createConfig([ConfigOptions::INPUT_KEY_CRYPT_KEY => 'key']);
        $this->assertNotEmpty($config['session']);
        $this->assertEquals('files', $config['session']->getData()['save']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid session save location.
     */
    public function testCreateConfigInvalidSessionSave()
    {
        $this->object->createConfig([ConfigOptions::INPUT_KEY_SESSION_SAVE => 'invalid']);
    }

    /**
     * @param array $options
     * @dataProvider createConfigNoKeyDataProvider
     */
    public function testCreateConfigNoKey(array $options)
    {
        $config = $this->object->createConfig($options);
        $this->assertEquals(md5('key'), $config['crypt']->getData()['key']);
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
