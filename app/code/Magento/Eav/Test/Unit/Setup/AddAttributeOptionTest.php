<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Setup;

use Magento\Eav\Setup\AddOptionToAttribute;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Coverage for \Magento\Eav\Setup\AddOptionToAttribute
 */
class AddAttributeOptionTest extends TestCase
{
    /**
     * @var AddOptionToAttribute
     */
    private $operation;

    /**
     * @var MockObject
     */
    private $connectionMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $setupMock = $this->getMockForAbstractClass(ModuleDataSetupInterface::class);
        $this->connectionMock = $this->createMock(Mysql::class);
        $this->connectionMock->method('select')
                             ->willReturn($objectManager->getObject(Select::class));

        $setupMock->method('getTable')->willReturn('some_table');
        $setupMock->method('getConnection')->willReturn($this->connectionMock);

        $this->operation = new AddOptionToAttribute($setupMock);
    }

    /**
     * @throws LocalizedException
     */
    public function testAddNewOptions()
    {
        $this->connectionMock->method('fetchAll')->willReturn([]);
        $this->connectionMock->expects($this->exactly(4))->method('insert');

        $this->operation->execute(
            [
                'values' => ['Black', 'White'],
                'attribute_id' => 4
            ]
        );
    }

    /**
     * @throws LocalizedException
     */
    public function testAddExistingOptionsWithTheSameSortOrder()
    {
        $this->connectionMock->method('fetchAll')->willReturn(
            [
                ['option_id' => 1, 'sort_order' => 0, 'value' => 'Black'],
                ['option_id' => 2, 'sort_order' => 1, 'value' => 'White'],
            ]
        );

        $this->connectionMock->expects($this->never())->method('insert');
        $this->connectionMock->expects($this->never())->method('update');

        $this->operation->execute(
            [
                'values' => ['Black', 'White'],
                'attribute_id' => 4
            ]
        );
    }

    /**
     * @throws LocalizedException
     */
    public function testAddExistingOptionsWithDifferentSortOrder()
    {
        $this->connectionMock->method('fetchAll')->willReturn(
            [
                ['option_id' => 1, 'sort_order' => 13, 'value' => 'Black'],
                ['option_id' => 2, 'sort_order' => 666, 'value' => 'White'],
            ]
        );

        $this->connectionMock->expects($this->never())->method('insert');
        $this->connectionMock->expects($this->exactly(2))->method('update');

        $this->operation->execute(
            [
                'values' => ['Black', 'White'],
                'attribute_id' => 4
            ]
        );
    }

    /**
     * @throws LocalizedException
     */
    public function testAddMixedOptions()
    {
        $this->connectionMock->method('fetchAll')->willReturn(
            [
                ['option_id' => 1, 'sort_order' => 13, 'value' => 'Black'],
            ]
        );

        $this->connectionMock->expects($this->exactly(2))->method('insert');
        $this->connectionMock->expects($this->once())->method('update');

        $this->operation->execute(
            [
                'values' => ['Black', 'White'],
                'attribute_id' => 4
            ]
        );
    }

    /**
     * @throws LocalizedException
     */
    public function testAddNewOption()
    {
        $this->connectionMock->expects($this->exactly(2))->method('insert');
        $this->connectionMock->expects($this->once())->method('delete');

        $this->operation->execute(
            [
                'attribute_id' => 1,
                'order' => [0 => 13],
                'value' => [
                    [
                        0 => 'zzz',
                    ],
                ],
            ]
        );
    }

    /**
     */
    public function testAddNewOptionWithoutDefaultValue()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The default option isn\'t defined. Set the option and try again.');

        $this->operation->execute(
            [
                'attribute_id' => 1,
                'order' => [0 => 13],
                'value' => [[]],
            ]
        );
    }

    public function testDeleteOption()
    {
        $this->connectionMock->expects($this->never())->method('insert');
        $this->connectionMock->expects($this->never())->method('update');
        $this->connectionMock->expects($this->once())->method('delete');

        $this->operation->execute(
            [
                'attribute_id' => 1,
                'delete' => [13 => true],
                'value' => [
                    13 => null,
                ],
            ]
        );
    }

    public function testUpdateOption()
    {
        $this->connectionMock->expects($this->once())->method('insert');
        $this->connectionMock->expects($this->once())->method('update');
        $this->connectionMock->expects($this->once())->method('delete');

        $this->operation->execute(
            [
                'attribute_id' => 1,
                'value' => [
                    13 => ['zzz'],
                ],
            ]
        );
    }
}
