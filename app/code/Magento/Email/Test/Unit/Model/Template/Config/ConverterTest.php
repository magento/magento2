<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model\Template\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Email\Model\Template\Config\Converter
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Email\Model\Template\Config\Converter();
    }

    public function testConvert()
    {
        $inputData = new \DOMDocument();
        $inputData->load(__DIR__ . '/_files/email_templates_merged.xml');
        $expectedResult = require __DIR__ . '/_files/email_templates_merged.php';
        $this->assertEquals($expectedResult, $this->_model->convert($inputData));
    }
}
