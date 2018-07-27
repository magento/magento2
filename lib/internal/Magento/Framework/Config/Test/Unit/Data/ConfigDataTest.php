<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit\Data;

use Magento\Framework\Config\Data\ConfigData;

class ConfigDataTest extends \PHPUnit_Framework_TestCase
{
    public function testSet()
    {
        $fileKey = 'testFileKey';
        $expectedValue = [
            'test' => [
                'path' => [
                    'value1' => 'val1',
                    'value2' => 'val4',
                    'value3' => 'val3',
                ]
            ]
        ];
        $configData = new ConfigData($fileKey);

        $configData->set('test/path/value1', 'val1');
        $configData->set('test/path/value2', 'val2');
        $configData->set('test/path/value3', 'val3');
        $configData->set('test/path/value2', 'val4');

        $this->assertEquals($expectedValue, $configData->getData());
        $this->assertEquals($fileKey, $configData->getFileKey());
    }

    /**
     * @param string $key
     * @param string $expectedException
     * @dataProvider setWrongKeyDataProvider
     */
    public function testSetWrongKey($key, $expectedException)
    {

        $configData = new ConfigData('testKey');

        $this->setExpectedException('InvalidArgumentException', $expectedException);
        $configData->set($key, 'value');
    }

    /**
     * @return array
     */
    public function setWrongKeyDataProvider()
    {
        return [
            'segment is empty' => [
                '/test/test/test',
                "Path '/test/test/test' is invalid. It cannot be empty nor start or end with '/'"
            ],
            'key is empty' => [
                '',
                "Path '' is invalid. It cannot be empty nor start or end with '/'"
            ],
            'access by empty value key' => [
                'test/',
                "Path 'test/' is invalid. It cannot be empty nor start or end with '/'"
            ]
        ];
    }
}
