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

    /**
     * @var ConfigDataGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generator;

    protected function setUp()
    {
        $this->generator = $this->getMock('\Magento\Setup\Model\ConfigDataGenerator', [], [], '', false);
        $this->object = new ConfigOptions($this->generator);
    }

    public function testGetOptions()
    {
        $options = $this->object->getOptions();
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[0]);
        $this->assertInstanceOf('Magento\Framework\Setup\Option\SelectConfigOption', $options[1]);
        $this->assertInstanceOf('Magento\Framework\Setup\Option\SelectConfigOption', $options[2]);
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[3]);
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[4]);
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[5]);
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[6]);
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[7]);
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[8]);
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[9]);
        $this->assertEquals(10, count($options));
    }

    public function testCreateOptions()
    {
        $this->generator->expects($this->once())->method('createInstallConfig');
        $this->generator->expects($this->once())->method('createCryptConfig');
        $this->generator->expects($this->once())->method('createModuleConfig');
        $this->generator->expects($this->once())->method('createSessionConfig');
        $this->generator->expects($this->once())->method('createDefinitionsConfig');
        $this->generator->expects($this->once())->method('createDbConfig');
        $this->generator->expects($this->once())->method('createResourceConfig');
        $this->object->createConfig([]);
    }
}
