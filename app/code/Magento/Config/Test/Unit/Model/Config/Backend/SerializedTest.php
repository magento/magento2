<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SerializedTest extends TestCase
{
    /** @var Serialized */
    private $serializedConfig;

    /** @var Json|MockObject */
    private $serializerMock;

    /** @var LoggerInterface|MockObject */
    private $loggerMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->serializerMock = $this->createMock(Json::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $contextMock = $this->createMock(Context::class);
        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $contextMock->method('getEventDispatcher')
            ->willReturn($eventManagerMock);
        $contextMock->method('getLogger')
            ->willReturn($this->loggerMock);
        $this->serializedConfig = $objectManager->getObject(
            Serialized::class,
            [
                'serializer' => $this->serializerMock,
                'context' => $contextMock,
                'config' => $this->scopeConfigMock,
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
    public static function afterLoadDataProvider()
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

    public function testAfterLoadWithException()
    {
        $value = '{"key":';
        $expected = false;
        $this->serializedConfig->setValue($value);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willThrowException(new \Exception());
        $this->loggerMock->expects($this->once())
            ->method('critical');
        $this->serializedConfig->afterLoad();
        $this->assertEquals($expected, $this->serializedConfig->getValue());
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
    public static function beforeSaveDataProvider()
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

    /**
     * If a config value is not available in core_confid_data the defaults are
     * loaded from the config.xml file. Those defaults may be arrays.
     * The Serialized backend model has to override its parent
     * getOldValue function, to prevent an array to string conversion error
     * and serialize those values.
     */
    public function testGetOldValueWithNonScalarDefaultValue(): void
    {
        $value = [
            ['foo' => '1', 'bar' => '2'],
        ];
        $serializedValue = \json_encode($value);

        $this->scopeConfigMock->method('getValue')->willReturn($value);
        $this->serializerMock->method('serialize')->willReturn($serializedValue);

        $this->serializedConfig->setData('value', $serializedValue);

        $oldValue = $this->serializedConfig->getOldValue();

        $this->assertIsString($oldValue, 'Default value from the config is not serialized.');
        $this->assertSame($serializedValue, $oldValue);
    }
}
