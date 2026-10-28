<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory;
use Magento\CatalogImportExport\Model\Import\Product\ImageTypeProcessor;
use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\TestCase;

class ImageTypeProcessorTest extends TestCase
{
    public function testGetImageTypes()
    {
        $resourceFactory = $this->createPartialMock(
            ResourceModelFactory::class,
            ['create']
        );

        $resource = $this->createPartialMock(ResourceModel::class, ['getTable', 'getConnection']);
        $resource->expects($this->once())
            ->method('getTable')
            ->with('eav_attribute')
            ->willReturnArgument(0);
        $connection = $this->createMock(AdapterInterface::class);
        $resource->method('getConnection')->willReturn($connection);
        $resourceFactory->expects($this->once())
            ->method('create')
            ->willReturn($resource);

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())
            ->method('from')
            ->with('eav_attribute', ['code' => 'attribute_code'], null)
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with('frontend_input = :frontend_input')
            ->willReturnSelf();
        $connection->method('fetchCol')->willReturn(['image', 'small_image', 'thumbnail', 'swatch_image']);
        $connection->method('select')->willReturn($selectMock);

        $typeProcessor = new ImageTypeProcessor($resourceFactory);
        $this->assertEquals(
            ['image', 'small_image', 'thumbnail', 'swatch_image', '_media_image'],
            $typeProcessor->getImageTypes()
        );
    }
}
