<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Framework\Serialize\Test\Unit\Serializer;

use Magento\Framework\Serialize\JsonConverter;
use PHPUnit\Framework\TestCase;

class JsonConverterTest extends TestCase
{
    public function testConvert()
    {
        $data = [
            'key' => 'value'
        ];

        $this->assertEquals(json_encode($data), JsonConverter::convert($data));
    }

    public function testConvertWithException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unable to serialize value.');
        //verify that exception will be thrown with invalid UTF8 sequence
        JsonConverter::convert("\xB1\x31");
    }
}
