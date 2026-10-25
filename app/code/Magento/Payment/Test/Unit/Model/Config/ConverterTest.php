<?php declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Payment\Test\Unit\Model\Config;

use Magento\Payment\Model\Config\Converter;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $_model;

    /** @var  array */
    protected $_targetArray;

    protected function setUp(): void
    {
        $this->_model = new Converter();
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
        $this->assertEquals($expectedResult, $this->_model->convert($dom), '');
    }
}
