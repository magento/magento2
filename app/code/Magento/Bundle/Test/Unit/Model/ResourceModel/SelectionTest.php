<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\ResourceModel;

use Codeception\PHPUnit\TestCase;
use Magento\Bundle\Model\ResourceModel\Selection as ResourceSelection;
use Magento\Bundle\Model\Selection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;

class SelectionTest extends TestCase
{
    /**
     * @var Context|Context&\PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\MockObject\MockObject
     */
    private Context $context;

    /**
     * @var MetadataPool|MetadataPool&\PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\MockObject\MockObject
     */
    private MetadataPool $metadataPool;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->context = $this->createMock(Context::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);
    }

    public function testSaveSelectionPrice()
    {
        $item = new Selection(
            $this->createMock(\Magento\Framework\Model\Context::class),
            $this->createMock(\Magento\Framework\Registry::class),
            $this->createMock(\Magento\Catalog\Helper\Data::class),
            $this->createMock(\Magento\Bundle\Model\ResourceModel\Selection::class)
        );

        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                'catalog_product_bundle_selection_price',
                $this->callback(function ($insertValues) {
                    return $insertValues['selection_price_type'] === 0 && $insertValues['selection_price_value'] === 0;
                }),
                ['selection_price_type', 'selection_price_value']
            );

        $parentResources = $this->createMock(ResourceConnection::class);
        $parentResources->expects($this->once())->method('getConnection')->willReturn($connection);
        $parentResources->expects($this->once())->method('getTableName')
            ->with('catalog_product_bundle_selection_price', 'test_connection_name')
            ->willReturn('catalog_product_bundle_selection_price');
        $this->context->expects($this->once())->method('getResources')->willReturn($parentResources);

        $selection = new ResourceSelection($this->context, $this->metadataPool, 'test_connection_name');
        $selection->saveSelectionPrice($item);
    }
}
