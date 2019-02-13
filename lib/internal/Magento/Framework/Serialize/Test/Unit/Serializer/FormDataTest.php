<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Serialize\Test\Unit\Serializer;

use Magento\Framework\Serialize\Serializer\FormData;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\InvalidArgumentException;

/**
 * Test for Magento\Framework\Serialize\Serializer\FormData class.
 */
class FormDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonSerializerMock;

    /**
     * @var FormData
     */
    private $formDataSerializer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->jsonSerializerMock = $this->createMock(Json::class);
        $this->formDataSerializer = new FormData($this->jsonSerializerMock);
    }

    /**
     * @param string $serializedData
     * @param array $encodedFields
     * @param array $expectedFormData
     * @return void
     * @dataProvider unserializeDataProvider
     */
    public function testUnserialize(string $serializedData, array $encodedFields, array $expectedFormData)
    {
        $this->jsonSerializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($encodedFields);

        $this->assertEquals($expectedFormData, $this->formDataSerializer->unserialize($serializedData));
    }

    /**
     * @return array
     */
    public function unserializeDataProvider(): array
    {
        return [
            [
                'serializedData' =>
                    '["option[order][option_0]=1","option[value][option_0]=1","option[delete][option_0]="]',
                'encodedFields' => [
                    'option[order][option_0]=1',
                    'option[value][option_0]=1',
                    'option[delete][option_0]=',
                    ],
                'expectedFormData' => [
                    'option' => [
                        'order' => [
                            'option_0' => '1',
                        ],
                        'value' => [
                            'option_0' => '1',
                        ],
                        'delete' => [
                            'option_0' => '',
                        ],
                    ],
                ],
            ],
            [
                'serializedData' => '[]',
                'encodedFields' => [],
                'expectedFormData' => [],
            ],
        ];
    }

    /**
     * @return void
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unable to unserialize value.
     */
    public function testUnserializeWithWrongSerializedData()
    {
        $serializedData = 'test';

        $this->jsonSerializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn('test');

        $this->formDataSerializer->unserialize($serializedData);
    }
}
