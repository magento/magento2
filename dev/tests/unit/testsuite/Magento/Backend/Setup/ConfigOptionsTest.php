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
     * @var ConfigOptionsList
     */
    private $object;

    protected function setUp()
    {
        $this->object = new ConfigOptionsList();
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
        $options = [ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'admin'];
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

    public function testValidate()
    {
        $options = [ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'admin'];
        $errors = $this->object->validate($options);
        $this->assertEmpty($errors);
    }

    /**
     * @param array $options
     * @param string $expectedError
     * @dataProvider validateInvalidDataProvider
     */
    public function testValidateInvalid(array $options, $expectedError)
    {
        $errors = $this->object->validate($options);
        $this->assertSame([$expectedError], $errors);
    }

    /**
     * @return array
     */
    public function validateInvalidDataProvider()
    {
        return [
            [[ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => '**'], "Invalid backend frontname '**'"],
            [
                [ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'invalid frontname'],
                "Invalid backend frontname 'invalid frontname'"
            ],
        ];
    }
}
