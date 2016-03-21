<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Plugin;

class ProductLinksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Plugin\ProductLinks
     */
    protected $model;

    /**
     * @var \Magento\CatalogInventory\Model\Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockHelperMock;

    protected function setUp()
    {
        $this->configMock = $this->getMock(
            'Magento\CatalogInventory\Model\Configuration',
            [],
            [],
            '',
            false
        );
        $this->stockHelperMock = $this->getMock(
            '\Magento\CatalogInventory\Helper\Stock',
            [],
            [],
            '',
            false
        );

        $this->model = new \Magento\CatalogInventory\Model\Plugin\ProductLinks(
            $this->configMock,
            $this->stockHelperMock
        );
    }

    /**
     * @dataProvider stockStatusDataProvider
     */
    public function testAfterGetProductCollectionShow($status, $callCount)
    {
        list($collectionMock, $subjectMock) = $this->buildMocks();
        $this->configMock->expects($this->once())->method('isShowOutOfStock')->will($this->returnValue($status));
        $this->stockHelperMock
            ->expects($this->exactly($callCount))
            ->method('addInStockFilterToCollection')
            ->with($collectionMock);

        $this->assertEquals($collectionMock, $this->model->afterGetProductCollection($subjectMock, $collectionMock));
    }

    private function buildMocks()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection $collectionMock */
        $collectionMock = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection',
            [],
            [],
            '',
            false
        );

        /** @var \Magento\Catalog\Model\Product\Link $subjectMock */
        $subjectMock = $this->getMock('Magento\Catalog\Model\Product\Link', [], [], '', false);
        return [$collectionMock, $subjectMock];
    }

    /**
     * @return array
     */
    public function stockStatusDataProvider()
    {
        return [
            [0, 1],
            [1, 0],
        ];
    }
}
