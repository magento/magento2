<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Http\Converter;

use Magento\Payment\Gateway\Http\Converter\HtmlFormConverter;

class HtmlFormConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConvert()
    {
        $expectedResult = [
            'parameter1' => 'val1',
            'parameter2' => 'val2',
            'parameter3' => 'val3'
        ];

        $converter = new HtmlFormConverter();
        static::assertEquals($expectedResult, $converter->convert($this->getValidFormHtml()));
    }

    public function testConvertNotValidHtml()
    {
        $converter = new HtmlFormConverter();
        $converter->convert('Not html. Really not.');
    }

    /**
     * Returns valid form HTML
     *
     * @return string
     */
    private function getValidFormHtml()
    {
        return '
        <!DOCTYPE HTML>
        <html>
         <head>
          <meta charset="utf-8">
          <title>Title</title>
         </head>
         <body>

         <form action="some">
          <p><input type="radio" name="parameter1" value="val1">val1<Br>
          <input type="radio" name="parameter2" value="val2">val2<Br>
          <input type="radio" name="parameter3" value="val3">val3</p>
          <p><input type="submit"></p>
         </form>

         </body>
        </html>
        ';
    }
}
