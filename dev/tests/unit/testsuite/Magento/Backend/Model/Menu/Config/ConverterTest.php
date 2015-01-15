<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Config\Converter
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Backend\Model\Menu\Config\Converter();
    }

    public function testConvertIfNodeHasAttribute()
    {
        $basePath = realpath(__DIR__) . '/../../_files/';
        $path = $basePath . 'menu_merged.xml';
        $domDocument = new \DOMDocument();
        $domDocument->load($path);
        $expectedData = include $basePath . 'menu_merged.php';
        $this->assertEquals($expectedData, $this->_model->convert($domDocument));
    }
}
