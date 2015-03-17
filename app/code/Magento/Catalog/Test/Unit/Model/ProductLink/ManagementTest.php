<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\ProductLink;

use Magento\Framework\Exception\NoSuchEntityException;

class ManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductLink\Management
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */

    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkInitializerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLinkFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkTypeProviderMock;

    protected function setUp()
    {
        $this->productRepositoryMock = $this->getMock('\Magento\Catalog\Model\ProductRepository', [], [], '', false);
        $this->productResourceMock = $this->getMock('\Magento\Catalog\Model\Resource\Product', [], [], '', false);
        $this->collectionProviderMock = $this->getMock(
            '\Magento\Catalog\Model\ProductLink\CollectionProvider',
            [],
            [],
            '',
            false
        );
        $this->linkInitializerMock = $this->getMock(
            '\Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks',
            [],
            [],
            '',
            false
        );
        $this->productLinkFactoryMock = $this->getMock(
            '\Magento\Catalog\Api\Data\ProductLinkInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);

        $this->linkTypeProviderMock = $this->getMock(
            '\Magento\Catalog\Model\Product\LinkTypeProvider',
            [],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Catalog\Model\ProductLink\Management',
            [
                'productRepository' => $this->productRepositoryMock,
                'collectionProvider' => $this->collectionProviderMock,
                'productLinkFactory' => $this->productLinkFactoryMock,
                'linkInitializer' => $this->linkInitializerMock,
                'productResource' => $this->productResourceMock,
                'linkTypeProvider' => $this->linkTypeProviderMock
            ]
        );
    }

    public function testGetLinkedItemsByType()
    {
        $productSku = 'product';
        $linkType = 'link';
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $item = [
            'sku' => 'product1',
            'type' => 'type1',
            'position' => 'pos1',
        ];
        $itemCollection = [$item];
        $this->collectionProviderMock->expects($this->once())
            ->method('getCollection')
            ->with($this->productMock, $linkType)
            ->willReturn($itemCollection);
        $this->productMock->expects($this->once())->method('getSku')->willReturn($productSku);
        $productLinkMock = $this->getMock('\Magento\Catalog\Api\Data\ProductLinkInterface');
        $productLinkMock->expects($this->once())
            ->method('setProductSku')
            ->with($productSku)
            ->willReturnSelf();
        $productLinkMock->expects($this->once())
            ->method('setLinkType')
            ->with($linkType)
            ->willReturnSelf();
        $productLinkMock->expects($this->once())
            ->method('setLinkedProductSku')
            ->with($item['sku'])
            ->willReturnSelf();
        $productLinkMock->expects($this->once())
            ->method('setLinkedProductType')
            ->with($item['type'])
            ->willReturnSelf();
        $productLinkMock->expects($this->once())
            ->method('setPosition')
            ->with($item['position'])
            ->willReturnSelf();
        $this->productLinkFactoryMock->expects($this->once())->method('create')->willReturn($productLinkMock);
        $this->assertEquals([$productLinkMock], $this->model->getLinkedItemsByType($productSku, $linkType));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Unknown link type: wrong_type
     */
    public function testGetLinkedItemsByTypeWithWrongType()
    {
        $productSku = 'product';
        $linkType = 'wrong_type';

        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $this->collectionProviderMock->expects($this->once())
            ->method('getCollection')
            ->with($this->productMock, $linkType)
            ->willThrowException(new NoSuchEntityException(__('Collection provider is not registered')));

        $this->model->getLinkedItemsByType($productSku, $linkType);
    }

    public function testSetProductLinks()
    {
        $type = 'type';
        $linkedProductsMock = [];
        $linksData = [];

        $this->linkTypeProviderMock->expects($this->once())->method('getLinkTypes')->willReturn([$type => 'link']);
        for ($i = 0; $i < 2; $i++) {
            $linkMock = $this->getMockForAbstractClass(
                '\Magento\Catalog\Api\Data\ProductLinkInterface',
                [],
                '',
                false,
                false,
                true,
                ['getLinkedProductSku', '__toArray']
            );
            $linkMock->expects($this->exactly(2))
                ->method('getLinkedProductSku')
                ->willReturn('linkedProduct' . $i . 'Sku');
            $linkMock->expects($this->once())->method('__toArray');
            $linkedProductsMock[$i] = $linkMock;
            $linksData['productSku']['link'][] = $linkMock;
        }
        $linkedSkuList = ['linkedProduct0Sku', 'linkedProduct1Sku'];
        $linkedProductIds = ['linkedProduct0Sku' => 1, 'linkedProduct1Sku' => 2];

        $this->productResourceMock->expects($this->once())->method('getProductsIdsBySkus')->with($linkedSkuList)
            ->willReturn($linkedProductIds);
        $this->productRepositoryMock->expects($this->once())->method('get')->willReturn($this->productMock);
        $this->linkInitializerMock->expects($this->once())->method('initializeLinks')
            ->with($this->productMock, [$type => [
                1 => ['product_id' => 1],
                2 => ['product_id' => 2],
            ]]);
        $this->productMock->expects($this->once())->method('save');
        $this->assertTrue($this->model->setProductLinks('', $type, $linkedProductsMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Provided link type "type2" does not exist
     */
    public function testSetProductLinksThrowExceptionIfProductLinkTypeDoesNotExist()
    {
        $type = 'type';
        $linkedProductsMock = [];

        $this->linkTypeProviderMock->expects($this->once())->method('getLinkTypes')->willReturn([$type => 'link']);
        $this->assertTrue($this->model->setProductLinks('', 'type2', $linkedProductsMock));
    }

    /**
     * @dataProvider setProductLinksNoProductExceptionDataProvider
     */
    public function testSetProductLinksNoProductException($exceptionName, $exceptionMessage, $linkedProductIds)
    {
        $this->setExpectedException($exceptionName, $exceptionMessage);
        $linkedProductsMock = [];
        $type = 'type';
        $this->linkTypeProviderMock->expects($this->once())->method('getLinkTypes')->willReturn([$type => 'link']);
        for ($i = 0; $i < 2; $i++) {
            $productLinkMock = $this->getMock(
                '\Magento\Catalog\Api\Data\ProductLinkInterface',
                [
                    'getLinkedProductSku', 'getProductSku', 'getLinkType',
                    '__toArray', 'getLinkedProductType', 'getPosition', 'getCustomAttribute', 'getCustomAttributes',
                    'setCustomAttribute', 'setCustomAttributes', 'getMetadataServiceInterface',
                    'getExtensionAttributes', 'setExtensionAttributes',
                    'setLinkedProductSku', 'setProductSku', 'setLinkType', 'setLinkedProductType', 'setPosition'
                ]
            );
            $productLinkMock->expects($this->any())
                ->method('getLinkedProductSku')
                ->willReturn('linkedProduct' . $i . 'Sku');
            $productLinkMock->expects($this->any())->method('getProductSku')->willReturn('productSku');
            $productLinkMock->expects($this->any())->method('getLinkType')->willReturn('link');
            $linkedProductsMock[$i] = $productLinkMock;
        }
        $linkedSkuList = ['linkedProduct0Sku', 'linkedProduct1Sku'];
        $this->productResourceMock->expects($this->any())->method('getProductsIdsBySkus')->with($linkedSkuList)
            ->willReturn($linkedProductIds);
        $this->model->setProductLinks('', $type, $linkedProductsMock);
    }

    public function setProductLinksNoProductExceptionDataProvider()
    {
        return [
            [
                '\Magento\Framework\Exception\NoSuchEntityException',
                'Product with SKU "linkedProduct0Sku" does not exist',
                ['linkedProduct1Sku' => 2],
            ], [
                '\Magento\Framework\Exception\NoSuchEntityException',
                'Product with SKU "linkedProduct1Sku" does not exist',
                ['linkedProduct0Sku' => 1]
            ]
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Invalid data provided for linked products
     */
    public function testSetProductLinksInvalidDataException()
    {
        $type = 'type';
        $linkedProductsMock = [];
        $linksData = [];
        $this->linkTypeProviderMock->expects($this->once())->method('getLinkTypes')->willReturn([$type => 'link']);
        for ($i = 0; $i < 2; $i++) {
            $linkMock = $this->getMockForAbstractClass(
                '\Magento\Catalog\Api\Data\ProductLinkInterface',
                [],
                '',
                false,
                false,
                true,
                ['getLinkedProductSku', '__toArray']
            );
            $linkMock->expects($this->exactly(2))
                ->method('getLinkedProductSku')
                ->willReturn('linkedProduct' . $i . 'Sku');
            $linkMock->expects($this->once())->method('__toArray');
            $linkedProductsMock[$i] = $linkMock;
            $linksData['productSku']['link'][] = $linkMock;
        }
        $linkedSkuList = ['linkedProduct0Sku', 'linkedProduct1Sku'];
        $linkedProductIds = ['linkedProduct0Sku' => 1, 'linkedProduct1Sku' => 2];

        $this->productResourceMock->expects($this->once())->method('getProductsIdsBySkus')->with($linkedSkuList)
            ->willReturn($linkedProductIds);
        $this->productRepositoryMock->expects($this->once())->method('get')->willReturn($this->productMock);
        $this->linkInitializerMock->expects($this->once())->method('initializeLinks')
            ->with($this->productMock, [$type => [
                1 => ['product_id' => 1],
                2 => ['product_id' => 2],
            ]]);
        $this->productMock->expects($this->once())->method('save')->willThrowException(new \Exception());
        $this->model->setProductLinks('', $type, $linkedProductsMock);
    }
}
