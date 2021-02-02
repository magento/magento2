<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * @subpackage  unit_tests
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Test\Unit\Model\Indexer\Stock\Plugin;

class StoreGroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Plugin\StoreGroup
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_indexerMock;

    protected function setUp(): void
    {
        $this->_indexerMock = $this->createMock(\Magento\CatalogInventory\Model\Indexer\Stock\Processor::class);
        $this->_model = new \Magento\CatalogInventory\Model\Indexer\Stock\Plugin\StoreGroup($this->_indexerMock);
    }

    /**
     * @param array $data
     * @dataProvider beforeSaveDataProvider
     */
    public function testBeforeSave(array $data)
    {
        $subjectMock = $this->createMock(\Magento\Store\Model\ResourceModel\Group::class);
        $objectMock = $this->createPartialMock(
            \Magento\Framework\Model\AbstractModel::class,
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
