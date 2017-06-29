<?php
/**
 * \Magento\Payment\Model\Config\Converter
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Payment\Model\Config\Converter
     */
    protected $_model;

    /** @var  array */
    protected $_targetArray;

    protected function setUp()
    {
        $this->_model = new \Magento\Payment\Model\Config\Converter();
    }

    public function testConvert()
    {
        $dom = new \DOMDocument();
        $xmlFile = __DIR__ . '/_files/payment.xml';
        $dom->loadXML(file_get_contents($xmlFile));

        $expectedResult = [
            'credit_cards' => ['SO' => 'Solo', 'SM' => 'Switch/Maestro'],
            'groups' => ['any_payment' => 'Any Payment'],
            'methods' => ['checkmo' => ['allow_multiple_address' => 1]],
        ];
        $this->assertEquals($expectedResult, $this->_model->convert($dom), '', 0, 20);
    }
}
