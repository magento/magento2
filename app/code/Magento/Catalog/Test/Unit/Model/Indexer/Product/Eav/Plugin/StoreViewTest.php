<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Plugin;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Indexer\Product\Eav\Plugin\StoreView;
use Magento\Catalog\Model\Indexer\Product\Eav\Processor;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Model\ResourceModel\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreViewTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Processor|MockObject
     */
    private $eavProcessorMock;
    /**
     * @var Store|MockObject
     */
    private $subjectMock;
    /**
     * @var StoreView
     */
    private $storeViewPlugin;

    protected function setUp(): void
    {
        $this->eavProcessorMock = $this->createMock(Processor::class);
        $this->subjectMock = $this->createMock(Store::class);
        $this->storeViewPlugin = new StoreView($this->eavProcessorMock);
    }

    /**
     * @param array $data
     */
    #[DataProvider('beforeSaveDataProvider')]
    public function testAfterSave(array $data): void
    {
        $matcher = $data['matcher'];

        $this->eavProcessorMock->expects($this->$matcher())
            ->method('markIndexerAsInvalid');

        $objectMock = $this->createPartialMockWithReflection(
            AbstractModel::class,
            ['isObjectNew', 'dataHasChangedFor', 'getIsActive']
        );
        $objectMock->method('isObjectNew')->willReturn(empty($data['object_id']));
        $objectMock->method('dataHasChangedFor')->with('group_id')
            ->willReturn($data['has_group_id_changed']);
        $objectMock->method('getIsActive')->willReturn($data['is_active']);

        $this->assertSame(
            $this->subjectMock,
            $this->storeViewPlugin->afterSave($this->subjectMock, $this->subjectMock, $objectMock)
        );
    }

    /**
     * @return array
     */
    public static function beforeSaveDataProvider(): array
    {
        return [
            [
                [
                    'matcher' => 'once',
                    'object_id' => 1,
                    'has_group_id_changed' => true,
                    'is_active' => true,
                ],
            ],
            [
                [
                    'matcher' => 'never',
                    'object_id' => 1,
                    'has_group_id_changed' => false,
                    'is_active' => true,
                ]
            ],
            [
                [
                    'matcher' => 'never',
                    'object_id' => 1,
                    'has_group_id_changed' => true,
                    'is_active' => false,
                ]
            ],
            [
                [
                    'matcher' => 'never',
                    'object_id' => 1,
                    'has_group_id_changed' => false,
                    'is_active' => false,
                ]
            ],
            [
                [
                    'matcher' => 'once',
                    'object_id' => 0,
                    'has_group_id_changed' => true,
                    'is_active' => true,
                ]
            ],
            [
                [
                    'matcher' => 'once',
                    'object_id' => 0,
                    'has_group_id_changed' => false,
                    'is_active' => true,
                ]
            ],
            [
                [
                    'matcher' => 'never',
                    'object_id' => 0,
                    'has_group_id_changed' => true,
                    'is_active' => false,
                ]
            ],
            [
                [
                    'matcher' => 'never',
                    'object_id' => 0,
                    'has_group_id_changed' => false,
                    'is_active' => false,
                ]
            ],
        ];
    }
}
