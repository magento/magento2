<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * @subpackage  unit_tests
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Indexer\Stock\Plugin;

use Magento\CatalogInventory\Model\Indexer\Stock\Plugin\StoreGroup;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ResourceModel\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreGroupTest extends TestCase
{
    /**
     * @var StoreGroup
     */
    protected $_model;

    /**
     * @var IndexerInterface|MockObject
     */
    protected $_indexerMock;

    protected function setUp(): void
    {
        $this->_indexerMock = $this->createMock(Processor::class);
        $this->_model = new StoreGroup($this->_indexerMock);
    }

    /**
     * @param array $data
     * @dataProvider beforeSaveDataProvider
     */
    public function testBeforeSave(array $data)
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

        $this->_indexerMock->expects($this->once())
            ->method('markIndexerAsInvalid');

        $this->_model->beforeSave($subjectMock, $objectMock);
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
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
