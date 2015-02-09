<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Route\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Route\Config\Converter
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\App\Route\Config\Converter();
    }

    public function testConvert()
    {
        $basePath = realpath(__DIR__) . '/_files/';
        $path = $basePath . 'routes.xml';
        $domDocument = new \DOMDocument();
        $domDocument->load($path);
        $expectedData = include $basePath . 'routes.php';
        $this->assertEquals($expectedData, $this->_model->convert($domDocument));
    }
}
