<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Unit\DataProvider\Modifier;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\DataProvider\Modifier\ModifierFactory;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\Pool;

/**
 * Class PoolTest
 */
class PoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ModifierFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $factoryMock;

    /**
     * @var ModifierInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dataProviderMockOne;

    /**
     * @var ModifierInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dataProviderMockTwo;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->factoryMock = $this->getMockBuilder(ModifierFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataProviderMockOne =
            $this->getMockBuilder(ModifierInterface::class)
                ->setMethods(['getData', 'getMeta', 'setData', 'setMeta'])
                ->getMockForAbstractClass();
        $this->dataProviderMockTwo = clone $this->dataProviderMockOne;

        $this->factoryMock->expects($this->any())
            ->method('create')
            ->willReturnMap([
                ['DataProviderMockOne', [], $this->dataProviderMockOne],
                ['DataProviderMockTwo', [], $this->dataProviderMockTwo],
            ]);
    }

    public function testWithOneDataProvider()
    {
        $expectedData = ['DataProviderMockOne' => $this->dataProviderMockOne];

        /** @var Pool $model */
        $model = $this->objectManager->getObject(Pool::class, [
            'factory' => $this->factoryMock,
            'modifiers' => [
                [
                    'class' => 'DataProviderMockOne',
                    'sortOrder' => 10,
                ],
            ]
        ]);

        $this->assertSame($expectedData, $model->getModifiersInstances());
    }

    public function testWithFewmodifiers()
    {
        $expectedData = [
            'DataProviderMockOne' => $this->dataProviderMockOne,
            'DataProviderMockTwo' => $this->dataProviderMockTwo,
        ];

        /** @var Pool $model */
        $model = $this->objectManager->getObject(Pool::class, [
            'factory' => $this->factoryMock,
            'modifiers' => [
                [
                    'class' => 'DataProviderMockOne',
                    'sortOrder' => 10,
                ],
                [
                    'class' => 'DataProviderMockTwo',
                    'sortOrder' => 20,
                ],
            ]
        ]);

        $this->assertSame($expectedData, $model->getModifiersInstances());
    }

    /**
     */
    public function testWithSortOrderException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The parameter "sortOrder" is missing. Set the "sortOrder" and try again.');

        /** @var Pool $model */
        $model = $this->objectManager->getObject(Pool::class, [
            'factory' => $this->factoryMock,
            'modifiers' => [
                [
                    'class' => 'DataProviderMockOne',
                ],
            ]
        ]);

        $model->getModifiersInstances();
    }

    /**
     */
    public function testWithClassException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The parameter "class" is missing. Set the "class" and try again.');

        /** @var Pool $model */
        $model = $this->objectManager->getObject(Pool::class, [
            'factory' => $this->factoryMock,
            'modifiers' => [
                [
                    'sortOrder' => 10,
                ],
            ]
        ]);

        $model->getModifiersInstances();
    }

    /**
     * @param array $modifiers
     * @param array $expectedResult
     * @dataProvider getModifiersDataProvider
     */
    public function testGetModifiers($modifiers, $expectedResult)
    {
        /** @var Pool $model */
        $model = $this->objectManager->getObject(Pool::class, [
            'factory' => $this->factoryMock,
            'modifiers' => $modifiers
        ]);

        $this->assertSame($model->getModifiers(), $expectedResult);
    }

    /**
     * @return array
     */
    public function getModifiersDataProvider()
    {
        return [
            [
                [
                    ['class' => 'DataProviderMockTwo', 'sortOrder' => 20],
                    ['class' => 'DataProviderMockOne', 'sortOrder' => 10]
                ],
                [
                    ['class' => 'DataProviderMockOne', 'sortOrder' => 10],
                    ['class' => 'DataProviderMockTwo', 'sortOrder' => 20]
                ],
            ],
            [
                [
                    ['class' => 'DataProviderMockOne', 'sortOrder' => 20],
                    ['class' => 'DataProviderMockFour', 'sortOrder' => 140],
                    ['class' => 'DataProviderMockTwo', 'sortOrder' => 31],
                    ['class' => 'DataProviderMockThree', 'sortOrder' => 77],
                ],
                [
                    ['class' => 'DataProviderMockOne', 'sortOrder' => 20],
                    ['class' => 'DataProviderMockTwo', 'sortOrder' => 31],
                    ['class' => 'DataProviderMockThree', 'sortOrder' => 77],
                    ['class' => 'DataProviderMockFour', 'sortOrder' => 140],
                ],
            ],
        ];
    }
}
