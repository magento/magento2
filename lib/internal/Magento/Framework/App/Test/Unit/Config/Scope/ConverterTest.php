<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config\Scope;

class ConverterTest extends \PHPUnit_Framework_TestCase
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
        $data = ['some/config/path1' => 'value1', 'some/config/path2' => 'value2'];
        $expectedResult = ['some' => ['config' => ['path1' => 'value1', 'path2' => 'value2']]];
        $this->assertEquals($expectedResult, $this->_model->convert($data));
    }
}
