<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ProductLink;

use Magento\Framework\Exception\NoSuchEntityException;

class ManagementTest extends \PHPUnit\Framework\TestCase
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
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkTypeProviderMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->productRepositoryMock = $this->createMock(\Magento\Catalog\Model\ProductRepository::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);

        $this->linkTypeProviderMock = $this->createMock(\Magento\Catalog\Model\Product\LinkTypeProvider::class);

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            \Magento\Catalog\Model\ProductLink\Management::class,
            [
                'productRepository' => $this->productRepositoryMock,
                'linkTypeProvider' => $this->linkTypeProviderMock
            ]
        );
    }

    public function testGetLinkedItemsByType()
    {
        $productSku = 'Simple Product 1';
        $linkType = 'related';
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);

        $inputRelatedLink = $this->objectManager->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
        $inputRelatedLink->setProductSku($productSku);
        $inputRelatedLink->setLinkType($linkType);
        $inputRelatedLink->setData("sku", "Simple Product 2");
        $inputRelatedLink->setData("type_id", "simple");
        $inputRelatedLink->setPosition(0);
        $links = [$inputRelatedLink];

        $linkTypes = ['related' => 1, 'upsell' => 4, 'crosssell' => 5, 'associated' => 3];
        $this->linkTypeProviderMock->expects($this->once())
            ->method('getLinkTypes')
            ->willReturn($linkTypes);

        $this->productMock->expects($this->once())->method('getProductLinks')->willReturn($links);
        $this->assertEquals($links, $this->model->getLinkedItemsByType($productSku, $linkType));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Unknown link type: bad type
     */
    public function testGetLinkedItemsByTypeWithWrongType()
    {
        $productSku = 'Simple Product 1';
        $linkType = 'bad type';
        $this->productRepositoryMock->expects($this->never())->method('get')->with($productSku)
            ->willReturn($this->productMock);

        $inputRelatedLink = $this->objectManager->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
        $inputRelatedLink->setProductSku($productSku);
        $inputRelatedLink->setLinkType($linkType);
        $inputRelatedLink->setData("sku", "Simple Product 2");
        $inputRelatedLink->setData("type_id", "simple");
        $inputRelatedLink->setPosition(0);
        $links = [$inputRelatedLink];

        $linkTypes = ['related' => 1, 'upsell' => 4, 'crosssell' => 5, 'associated' => 3];
        $this->linkTypeProviderMock->expects($this->once())
            ->method('getLinkTypes')
            ->willReturn($linkTypes);

        $this->productMock->expects($this->never())->method('getProductLinks')->willReturn($links);
        $this->model->getLinkedItemsByType($productSku, $linkType);
    }

    public function testSetProductLinks()
    {
        $productSku = 'Simple Product 1';
        $linkType = 'related';
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);

        $inputRelatedLink = $this->objectManager->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
        $inputRelatedLink->setProductSku($productSku);
        $inputRelatedLink->setLinkType($linkType);
        $inputRelatedLink->setData("sku", "Simple Product 1");
        $inputRelatedLink->setData("type_id", "related");
        $inputRelatedLink->setPosition(0);
        $links = [$inputRelatedLink];

        $linkTypes = ['related' => 1, 'upsell' => 4, 'crosssell' => 5, 'associated' => 3];
        $this->linkTypeProviderMock->expects($this->once())
            ->method('getLinkTypes')
            ->willReturn($linkTypes);

        $this->productMock->expects($this->once())->method('getProductLinks')->willReturn([]);
        $this->productMock->expects($this->once())->method('setProductLinks')->with($links);
        $this->assertTrue($this->model->setProductLinks($productSku, $links));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage linkType is a required field.
     */
    public function testSetProductLinksWithoutLinkTypeInLink()
    {
        $productSku = 'Simple Product 1';

        $inputRelatedLink = $this->objectManager->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
        $inputRelatedLink->setProductSku($productSku);
        $inputRelatedLink->setData("sku", "Simple Product 1");
        $inputRelatedLink->setPosition(0);
        $links = [$inputRelatedLink];

        $linkTypes = ['related' => 1, 'upsell' => 4, 'crosssell' => 5, 'associated' => 3];
        $this->linkTypeProviderMock->expects($this->once())
            ->method('getLinkTypes')
            ->willReturn($linkTypes);

        $this->assertTrue($this->model->setProductLinks($productSku, $links));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Provided link type "bad type" does not exist
     */
    public function testSetProductLinksThrowExceptionIfProductLinkTypeDoesNotExist()
    {
        $productSku = 'Simple Product 1';
        $linkType = 'bad type';
        $this->productRepositoryMock->expects($this->never())->method('get')->with($productSku)
            ->willReturn($this->productMock);

        $inputRelatedLink = $this->objectManager->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
        $inputRelatedLink->setProductSku($productSku);
        $inputRelatedLink->setLinkType($linkType);
        $inputRelatedLink->setData("sku", "Simple Product 2");
        $inputRelatedLink->setData("type_id", "simple");
        $inputRelatedLink->setPosition(0);
        $links = [$inputRelatedLink];

        $linkTypes = ['related' => 1, 'upsell' => 4, 'crosssell' => 5, 'associated' => 3];
        $this->linkTypeProviderMock->expects($this->once())
            ->method('getLinkTypes')
            ->willReturn($linkTypes);

        $this->assertTrue($this->model->setProductLinks('', $links));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested product doesn't exist
     */
    public function testSetProductLinksNoProductException()
    {
        $productSku = 'Simple Product 1';
        $linkType = 'related';

        $inputRelatedLink = $this->objectManager->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
        $inputRelatedLink->setProductSku($productSku);
        $inputRelatedLink->setLinkType($linkType);
        $inputRelatedLink->setData("sku", "Simple Product 2");
        $inputRelatedLink->setData("type_id", "simple");
        $inputRelatedLink->setPosition(0);
        $links = [$inputRelatedLink];

        $linkTypes = ['related' => 1, 'upsell' => 4, 'crosssell' => 5, 'associated' => 3];
        $this->linkTypeProviderMock->expects($this->once())
            ->method('getLinkTypes')
            ->willReturn($linkTypes);

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->will(
                $this->throwException(
                    new \Magento\Framework\Exception\NoSuchEntityException(__('Requested product doesn\'t exist'))
                )
            );
        $this->model->setProductLinks($productSku, $links);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Invalid data provided for linked products
     */
    public function testSetProductLinksInvalidDataException()
    {
        $productSku = 'Simple Product 1';
        $linkType = 'related';
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);

        $inputRelatedLink = $this->objectManager->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
        $inputRelatedLink->setProductSku($productSku);
        $inputRelatedLink->setLinkType($linkType);
        $inputRelatedLink->setData("sku", "bad sku");
        $inputRelatedLink->setData("type_id", "bad type");
        $inputRelatedLink->setPosition(0);
        $links = [$inputRelatedLink];

        $linkTypes = ['related' => 1, 'upsell' => 4, 'crosssell' => 5, 'associated' => 3];
        $this->linkTypeProviderMock->expects($this->once())
            ->method('getLinkTypes')
            ->willReturn($linkTypes);

        $this->productMock->expects($this->once())->method('getProductLinks')->willReturn([]);

        $this->productRepositoryMock->expects($this->once())->method('save')->willThrowException(new \Exception());
        $this->model->setProductLinks($productSku, $links);
    }
}
