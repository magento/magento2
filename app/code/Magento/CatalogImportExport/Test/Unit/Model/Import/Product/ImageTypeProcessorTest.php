<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product\ImageTypeProcessor;

class ImageTypeProcessorTest extends \PHPUnit\Framework\TestCase
{
    public function testGetImageTypes()
    {
        $resourceFactory = $this->createPartialMock(
            \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory::class,
            ['create']
        );

        $resource = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTable', 'getConnection'])
            ->getMock();
        $resource->expects($this->once())
            ->method('getTable')
            ->with('eav_attribute')
            ->willReturnArgument(0);
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);
        $resourceFactory->expects($this->once())
            ->method('create')
            ->willReturn($resource);

        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock->expects($this->once())
            ->method('from')
            ->with('eav_attribute', ['code' => 'attribute_code'], null)
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with('frontend_input = :frontend_input')
            ->willReturnSelf();
        $connection->expects($this->any())
            ->method('fetchCol')
            ->willReturn(['image', 'small_image', 'thumbnail', 'swatch_image']);
        $connection->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);

        $typeProcessor = new ImageTypeProcessor($resourceFactory);
        $this->assertEquals(
            ['image', 'small_image', 'thumbnail', 'swatch_image', '_media_image'],
            $typeProcessor->getImageTypes()
        );
    }
}
