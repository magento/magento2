<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\GraphQl\Test\Unit\Query;

use Magento\Framework\Config\DataInterface;
use Magento\Framework\GraphQl\Config\ConfigElementFactoryInterface;
use Magento\Framework\GraphQl\Config\Element\Enum;
use Magento\Framework\GraphQl\Config\Element\EnumValue;
use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\GraphQl\Query\Fields as QueryFields;
use Magento\Framework\GraphQl\Schema\Type\Enum\DataMapperInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Framework\GraphQl\Query\EnumLookup
 */
class EnumLookupTest extends TestCase
{
    private const ENUM_NAME = 'SubscriptionStatusesEnum';

    /**
     * @var DataInterface|MockObject
     */
    private $configDataMock;

    /**
     * @var ConfigElementFactoryInterface|MockObject
     */
    private $configElementFactoryMock;

    /**
     * @var DataMapperInterface|MockObject
     */
    private $enumDataMapperMock;

    /**
     * Testable Object
     *
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * @var Enum|MockObject
     */
    private $enumMock;

    /**
     * Object Manager Instance
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var QueryFields|MockObject
     */
    private $queryFieldsMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private $typeConfigMock;

    /**
     * @var array
     */
    private $map = [];

    /**
     * @var array
     */
    private $values = [];

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->map = [
            self::ENUM_NAME => [
                'subscribed' => '1',
                'not_active' => '2',
                'unsubscribed' => '3',
                'unconfirmed' => '4',
            ]
        ];

        $this->values = [
            'NOT_ACTIVE' => new EnumValue('not_active', 'NOT_ACTIVE'),
            'SUBSCRIBED' => new EnumValue('subscribed', 'SUBSCRIBED'),
            'UNSUBSCRIBED' => new EnumValue('unsubscribed', 'UNSUBSCRIBED'),
            'UNCONFIRMED' => new EnumValue('unconfirmed', 'UNCONFIRMED'),
        ];

        $this->enumMock = $this->getMockBuilder(Enum::class)
            ->setConstructorArgs(
                [
                    self::ENUM_NAME,
                    $this->values,
                    'Subscription statuses',
                ]
            )
            ->getMock();

        $this->enumDataMapperMock = $this->createMock(DataMapperInterface::class);
        $this->configDataMock = $this->createMock(DataInterface::class);
        $this->configElementFactoryMock = $this->createMock(ConfigElementFactoryInterface::class);
        $this->queryFieldsMock = $this->createMock(QueryFields::class);
        $this->typeConfigMock = $this->createMock(ConfigInterface::class);

        $this->enumLookup = $this->objectManager->getObject(
            EnumLookup::class,
            [
                'typeConfig' => $this->typeConfigMock,
                'enumDataMapper' => $this->enumDataMapperMock,
            ]
        );
    }

    public function testGetEnumValueFromField()
    {
        $enumName = self::ENUM_NAME;
        $fieldValue = '1';

        $this->enumDataMapperMock
            ->expects($this->once())
            ->method('getMappedEnums')
            ->willReturn($this->map[$enumName]);

        $this->typeConfigMock
            ->expects($this->once())
            ->method('getConfigElement')
            ->willReturn($this->enumMock);

        $this->enumMock
            ->expects($this->once())
            ->method('getValues')
            ->willReturn($this->values);

        $this->assertEquals(
            'SUBSCRIBED',
            $this->enumLookup->getEnumValueFromField($enumName, $fieldValue)
        );
    }
}
