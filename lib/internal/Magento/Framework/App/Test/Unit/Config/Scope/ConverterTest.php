<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Config\Scope;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Config\Scope\Converter
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\App\Config\Scope\Converter();
    }

    public function testConvert()
    {
        $data = [
            'some/config/path1' => 'value1',
            'some/config/path2' => 'value2',
            'some/config/path2' => 'value3',
            'some2/config/path2' => 'value4',
            'some/bad/path////' => 'value5',
        ];
        $expectedResult = [
            'some' => [
                'config' => [
                    'path1' => 'value1',
                    'path2' => 'value3',
                ],
                'bad' => [
                    'path' => 'value5',
                ],
            ],
            'some2' => [
                'config' => [
                    'path2' => 'value4',
                ]
            ]
        ];
        $this->assertEquals($expectedResult, $this->_model->convert($data));
    }
}
