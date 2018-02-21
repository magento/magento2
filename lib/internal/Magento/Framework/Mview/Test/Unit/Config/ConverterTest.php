<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\Test\Unit\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Mview\Config\Converter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Mview\Config\Converter();
    }

    public function testConvert()
    {
        $data = include __DIR__ . '/../_files/mview_config.php';
        $dom = new \DOMDocument();
        $dom->loadXML($data['inputXML']);

        $this->assertEquals($data['expected'], $this->_model->convert($dom));
    }
}
