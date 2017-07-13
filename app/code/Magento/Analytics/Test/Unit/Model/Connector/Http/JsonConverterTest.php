<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector\Http;

use Magento\Analytics\Model\Connector\Http\JsonConverter;

/**
 * Class JsonConverterTest
 */
class JsonConverterTest extends \PHPUnit\Framework\TestCase
{
    public function testConverterContainsHeader()
    {
        $converter = new JsonConverter();
        $this->assertEquals(JsonConverter::CONTENT_TYPE_HEADER, $converter->getContentTypeHeader());
    }

    public function testConvertBody()
    {
        $body = '{"token": "secret-token"}';
        $converter = new JsonConverter();
        $this->assertEquals(json_decode($body, 1), $converter->fromBody($body));
    }

    public function testConvertData()
    {
        $data = ["token" => "secret-token"];
        $converter = new JsonConverter();
        $this->assertEquals(json_encode($data), $converter->toBody($data));
    }
}
