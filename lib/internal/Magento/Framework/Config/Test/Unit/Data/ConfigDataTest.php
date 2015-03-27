<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
                    'value1' => '1',
                    'value2' => '4',
                    'value3' => '3',
                ]
            ]
        ];
        $configData = new ConfigData($fileKey);

        $configData->set('test/path/value1', '1');
        $configData->set('test/path/value2', '2');
        $configData->set('test/path/value3', '3');
        $configData->set('test/path/value2', '4');

        $this->assertEquals($expectedValue, $configData->getData());
        $this->assertEquals($fileKey, $configData->getFileKey());
    }

    /**
     * @param string $key
     * @param string $expectedException
     * @dataProvider exceptionDataProvider
     */
    public function testSetWrongKey($key, $expectedException) {

        $configData = new ConfigData('testKey');

        $this->setExpectedException('InvalidArgumentException', $expectedException);
        $configData->set($key, 'value');
    }

    public function exceptionDataProvider()
    {
        return [
            'segment is empty' => [
                '/test/test/test',
                "The path '/test/test/test' is invalid, it should not be empty and started or ended with '/' symbol"
            ],
            'key is empty' => [
                '',
                "The path '' is invalid, it should not be empty and started or ended with '/' symbol"
            ],
            'access by empty value key' => [
                'test/',
                "The path 'test/' is invalid, it should not be empty and started or ended with '/' symbol"
            ]
        ];
    }
}
