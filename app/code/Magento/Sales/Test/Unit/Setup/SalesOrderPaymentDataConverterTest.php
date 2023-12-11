<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Setup;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Setup\SalesOrderPaymentDataConverter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SalesOrderPaymentDataConverterTest extends TestCase
{
    /**
     * @var Serialize|MockObject
     */
    private $serializeMock;

    /**
     * @var Json|MockObject
     */
    private $jsonMock;

    /**
     * @var SalesOrderPaymentDataConverter
     */
    private $salesOrderPaymentDataConverter;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->serializeMock = $this->createMock(Serialize::class);
        $this->jsonMock = $this->createMock(Json::class);
        $this->salesOrderPaymentDataConverter = $objectManager->getObject(
            SalesOrderPaymentDataConverter::class,
            [
                'serialize' => $this->serializeMock,
                'json' => $this->jsonMock
            ]
        );
    }

    public function testConvert()
    {
        $serializedData = 'serialized data';
        $unserializedData = [
            'token_metadata' => [
                'customer_id' => 1,
                'public_hash' => 'someHash'
            ]
        ];
        $convertedUnserializedData = [
            'customer_id' => 1,
            'public_hash' => 'someHash'
        ];
        $jsonEncodedData = 'json encoded data';

        $this->serializeMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($unserializedData);
        $this->jsonMock->expects($this->once())
            ->method('serialize')
            ->with($convertedUnserializedData)
            ->willReturn($jsonEncodedData);

        $this->assertEquals($jsonEncodedData, $this->salesOrderPaymentDataConverter->convert($serializedData));
    }
}
