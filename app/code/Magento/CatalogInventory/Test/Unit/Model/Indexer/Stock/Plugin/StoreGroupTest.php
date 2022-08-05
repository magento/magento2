<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Indexer\Stock\Plugin;

use Magento\CatalogInventory\Model\Indexer\Stock\Plugin\StoreGroup;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ResourceModel\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreGroupTest extends TestCase
{
    /**
     * @var StoreGroup
     */
    private $model;

    /**
     * @var Processor|MockObject
     */
    private $indexerProcessorMock;

    protected function setUp(): void
    {
        $this->indexerProcessorMock = $this->createMock(Processor::class);
        $this->model = new StoreGroup($this->indexerProcessorMock);
    }

    /**
     * @param array $data
     * @dataProvider afterSaveDataProvider
     */
    public function testAfterSave(array $data): void
    {
        $subjectMock = $this->createMock(Group::class);
        $objectMock = $this->createPartialMock(
            AbstractModel::class,
            ['getId', 'dataHasChangedFor', '__wakeup']
        );
        $objectMock->expects($this->once())
            ->method('getId')
            ->willReturn($data['object_id']);
        $objectMock->expects($this->any())
            ->method('dataHasChangedFor')
            ->with('website_id')
            ->willReturn($data['has_website_id_changed']);

        $this->indexerProcessorMock->expects($this->once())
            ->method('markIndexerAsInvalid');

        $this->assertSame(
            $subjectMock,
            $this->model->afterSave($subjectMock, $subjectMock, $objectMock)
        );
    }

    /**
     * @return array
     */
    public function afterSaveDataProvider(): array
    {
        return [
            [
                [
                    'object_id' => 1,
                    'has_website_id_changed' => true,
                ],
            ],
            [
                [
                    'object_id' => false,
                    'has_website_id_changed' => true,
                ]
            ],
            [
                [
                    'object_id' => false,
                    'has_website_id_changed' => false,
                ]
            ],
        ];
    }
}
