<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Backend\Serialized;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit coverage for FieldArray config persistence via ArraySerialized.
 *
 * When a dependent FieldArray is hidden, the browser must not post the field at all.
 * If only the __empty sentinel is posted, beforeSave collapses that to an empty array
 * (intentional "clear all rows" when the field is visible).
 */
class ArraySerializedTest extends TestCase
{
    /**
     * @var ArraySerialized
     */
    private $model;

    /**
     * @var Json|MockObject
     */
    private $serializer;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->serializer = $this->createMock(Json::class);
        $contextMock = $this->createMock(Context::class);
        $eventManagerMock = $this->createMock(ManagerInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $contextMock->method('getEventDispatcher')->willReturn($eventManagerMock);
        $contextMock->method('getLogger')->willReturn($loggerMock);

        $this->model = $objectManager->getObject(
            ArraySerialized::class,
            [
                'serializer' => $this->serializer,
                'context' => $contextMock,
            ]
        );
    }

    /**
     * Full grid POST (rows + FieldArray sentinel) must drop __empty and keep row data.
     */
    public function testBeforeSaveKeepsRowsAndStripsEmptySentinel(): void
    {
        $posted = [
            '_row1' => ['item_label' => 'alpha', 'item_data' => 'one'],
            '_row2' => ['item_label' => 'beta', 'item_data' => 'two'],
            '__empty' => '',
        ];
        $expected = [
            '_row1' => ['item_label' => 'alpha', 'item_data' => 'one'],
            '_row2' => ['item_label' => 'beta', 'item_data' => 'two'],
        ];

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($expected)
            ->willReturn('{"_row1":{"item_label":"alpha","item_data":"one"},'
                . '"_row2":{"item_label":"beta","item_data":"two"}}');

        $this->model->setValue($posted);
        $this->model->beforeSave();

        $this->assertSame(
            '{"_row1":{"item_label":"alpha","item_data":"one"},'
            . '"_row2":{"item_label":"beta","item_data":"two"}}',
            $this->model->getValue()
        );
    }

    /**
     * Only the FieldArray __empty sentinel posted (visible field, all rows removed)
     * must serialize to an empty array — intentional clear.
     *
     * The same payload is what dependence used to post when the field was hidden
     * and only the always-enabled hidden sentinel remained submittable.
     */
    public function testBeforeSaveOnlyEmptySentinelSerializesToEmptyArray(): void
    {
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with([])
            ->willReturn('[]');

        $this->model->setValue(['__empty' => '']);
        $this->model->beforeSave();

        $this->assertSame('[]', $this->model->getValue());
    }

    /**
     * @param mixed $value
     * @param mixed $expectedAfterUnset
     * @param bool $serializeExpected
     */
    #[DataProvider('beforeSaveDataProvider')]
    public function testBeforeSaveDataProviderCases($value, $expectedAfterUnset, bool $serializeExpected): void
    {
        if ($serializeExpected) {
            $this->serializer->expects($this->once())
                ->method('serialize')
                ->with($expectedAfterUnset)
                ->willReturn(json_encode($expectedAfterUnset));
        } else {
            $this->serializer->expects($this->never())->method('serialize');
        }

        $this->model->setValue($value);
        $this->model->beforeSave();

        if ($serializeExpected) {
            $this->assertSame(json_encode($expectedAfterUnset), $this->model->getValue());
        } else {
            $this->assertSame($value, $this->model->getValue());
        }
    }

    /**
     * @return array
     */
    public static function beforeSaveDataProvider(): array
    {
        return [
            'empty array without sentinel' => [
                [],
                [],
                true,
            ],
            'rows without sentinel' => [
                ['_r' => ['item_label' => 'x']],
                ['_r' => ['item_label' => 'x']],
                true,
            ],
            'non-array string is left to parent as-is' => [
                'already-serialized',
                null,
                false,
            ],
            'null value is left as-is' => [
                null,
                null,
                false,
            ],
        ];
    }

    public function testAfterLoadUnserializesJsonIntoArray(): void
    {
        $json = '{"_row1":{"item_label":"alpha"}}';
        $decoded = ['_row1' => ['item_label' => 'alpha']];

        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($json)
            ->willReturn($decoded);

        $this->model->setValue($json);
        $this->model->afterLoad();

        $this->assertSame($decoded, $this->model->getValue());
    }

    public function testAfterLoadEmptyStringBecomesFalse(): void
    {
        $this->serializer->expects($this->never())->method('unserialize');

        $this->model->setValue('');
        $this->model->afterLoad();

        $this->assertFalse($this->model->getValue());
    }
}
