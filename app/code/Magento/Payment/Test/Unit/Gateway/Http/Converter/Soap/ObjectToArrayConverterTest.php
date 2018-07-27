<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Http\Converter\Soap;

use Magento\Payment\Gateway\Http\Converter\Soap\ObjectToArrayConverter;

class ObjectToArrayConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConvert()
    {
        $input = new \stdClass();
        $input->property = new \stdClass();
        $input->property2 = 'bla';
        $input->property->property3 = new \stdClass();
        $input->property->property4 = 'bla';
        $input->property->property3->property5 = 'bla';

        $output = [
            'property' => [
                'property3' => [
                    'property5' => 'bla'
                ],
                'property4' => 'bla'
            ],
            'property2' => 'bla'
        ];

        $converter = new ObjectToArrayConverter();
        static::assertEquals($output, $converter->convert($input));

    }
}
