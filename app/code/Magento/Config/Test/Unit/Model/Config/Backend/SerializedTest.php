<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized;
use Magento\Framework\Model\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SerializedTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Config\Model\Config\Backend\Serialized */
    private $serializedConfig;

    /** @var Json|\PHPUnit_Framework_MockObject_MockObject */
    private $serializerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->serializerMock = $this->createMock(Json::class);
        $contextMock = $this->createMock(Context::class);
        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $contextMock->method('getEventDispatcher')
            ->willReturn($eventManagerMock);
        $this->serializedConfig = $objectManager->getObject(
            Serialized::class,
            [
                'serializer' => $this->serializerMock,
                'context' => $contextMock,
            ]
        );
    }

    /**
     * @param int|double|string|array|boolean|null $expected
     * @param int|double|string|array|boolean|null $value
     * @param int $numCalls
     * @param array $unserializedValue
     * @dataProvider afterLoadDataProvider
     */
    public function testAfterLoad($expected, $value, $numCalls, $unserializedValue = null)
    {
        $this->serializedConfig->setValue($value);
        $this->serializerMock->expects($this->exactly($numCalls))
            ->method('unserialize')
            ->willReturn($unserializedValue);
        $this->serializedConfig->afterLoad();
        $this->assertEquals($expected, $this->serializedConfig->getValue());
    }

    /**
     * @return array
     */
    public function afterLoadDataProvider()
    {
        return [
            'empty value' => [
                false,
                '',
                0,
            ],
            'value' => [
                ['string array'],
                'string array',
                1,
                ['string array']
            ]
        ];
    }

    /**
     * @param string $expected
     * @param int|double|string|array|boolean|null $value
     * @param int $numCalls
     * @param string|null $serializedValue
     * @dataProvider beforeSaveDataProvider
     */
    public function testBeforeSave($expected, $value, $numCalls, $serializedValue = null)
    {
        $this->serializedConfig->setId('id');
        $this->serializedConfig->setValue($value);
        $this->serializerMock->expects($this->exactly($numCalls))
            ->method('serialize')
            ->willReturn($serializedValue);
        $this->serializedConfig->beforeSave();
        $this->assertEquals($expected, $this->serializedConfig->getValue());
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            'string' => [
                'string',
                'string',
                0,
            ],
            'array' => [
                'string array',
                ['string array'],
                1,
                'string array'
            ]
        ];
    }
}
