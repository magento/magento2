<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Serialize\Test\Unit\Serializer;

<<<<<<< HEAD
=======
/**
 * Class JsonConverterTest
 */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
class JsonConverterTest extends \PHPUnit\Framework\TestCase
{
    public function testConvert()
    {
        $data = [
            'key' => 'value'
        ];

        $this->assertEquals(json_encode($data), \Magento\Framework\Serialize\JsonConverter::convert($data));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable to serialize value.
     */
    public function testConvertWithException()
    {
        //verify that exception will be thrown with invalid UTF8 sequence
        \Magento\Framework\Serialize\JsonConverter::convert("\xB1\x31");
    }
}
