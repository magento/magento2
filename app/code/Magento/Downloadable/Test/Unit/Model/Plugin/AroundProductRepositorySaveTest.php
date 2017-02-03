<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Downloadable\Test\Unit\Model\Plugin;

use \Magento\Downloadable\Model\Plugin\AroundProductRepositorySave;

class AroundProductRepositorySaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AroundProductRepositorySave
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sampleRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $savedProductMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productExtensionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $existingProductExtensionMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp()
    {
        $this->productRepositoryMock = $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface');
        $this->linkRepositoryMock = $this->getMock('Magento\Downloadable\Api\LinkRepositoryInterface');
        $this->sampleRepositoryMock = $this->getMock('Magento\Downloadable\Api\SampleRepositoryInterface');
        $this->productMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
        $this->savedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->closureMock = function () {
            return $this->savedProductMock;
        };
        $this->model = new AroundProductRepositorySave(
            $this->linkRepositoryMock,
            $this->sampleRepositoryMock        );
        $this->productExtensionMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductExtension')
            ->setMethods(['getDownloadableProductLinks', 'getDownloadableProductSamples'])->getMock();
        $this->existingProductExtensionMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductExtension')
            ->setMethods(['getDownloadableProductLinks', 'getDownloadableProductSamples'])
            ->getMock();
    }

    public function testAroundSaveWhenProductIsSimple()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->productMock->expects($this->never())->method('getExtensionAttributes');

        $this->assertEquals(
            $this->savedProductMock,
            $this->model->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }

    public function testAroundSaveWhenProductHasNoExtensionAttributes()
    {
        $this->productMock->expects($this->once())->method('getTypeId')
            ->willReturn(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE);
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(null);

        $this->savedProductMock->expects($this->never())->method('getExtensionAttributes');
        $this->linkRepositoryMock->expects($this->never())->method('save');

        $this->assertEquals(
            $this->savedProductMock,
            $this->model->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }

    /**
     * Input has two links and two samples, one existing and one new
     * Existing product has two links and two samples, one will be updated and one will be deleted
     */
    public function testAroundSave()
    {
        $productSku = "downloadable_product";
        $existingLinkId = '2';
        $existingSampleId = '5';
        $toBeDeletedLinkId = '3';
        $toBeDeletedSampleId = '4';

        $this->productMock->expects($this->once())->method('getTypeId')
            ->willReturn(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE);
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $updateLinkMock = $this->getMock('\Magento\Downloadable\Api\Data\LinkInterface');
        $updateLinkMock->expects($this->once())->method('getId')->willReturn($existingLinkId);
        $newLinkMock = $this->getMock('\Magento\Downloadable\Api\Data\LinkInterface');
        $newLinkMock->expects($this->once())->method('getId')->willReturn(null);
        $this->productExtensionMock->expects($this->once())
            ->method('getDownloadableProductLinks')
            ->willReturn([$newLinkMock, $updateLinkMock]);

        $updateSampleMock = $this->getMock('\Magento\Downloadable\Api\Data\SampleInterface');
        $updateSampleMock->expects($this->once())->method('getId')->willReturn($existingSampleId);
        $newSampleMock = $this->getMock('\Magento\Downloadable\Api\Data\SampleInterface');
        $newSampleMock->expects($this->once())->method('getId')->willReturn(null);
        $this->productExtensionMock->expects($this->once())
            ->method('getDownloadableProductSamples')
            ->willReturn([$updateSampleMock, $newSampleMock]);

        $existingLinkMock = $this->getMock('\Magento\Downloadable\Api\Data\LinkInterface');
        $existingLinkMock->expects($this->once())->method('getId')->willReturn($existingLinkId);
        $toBeDeletedLinkMock = $this->getMock('\Magento\Downloadable\Api\Data\LinkInterface');
        $toBeDeletedLinkMock->expects($this->once())->method('getId')->willReturn($toBeDeletedLinkId);

        $existingSampleMock = $this->getMock('\Magento\Downloadable\Api\Data\SampleInterface');
        $existingSampleMock->expects($this->once())->method('getId')->willReturn($existingSampleId);
        $toBeDeletedSampleMock = $this->getMock('\Magento\Downloadable\Api\Data\SampleInterface');
        $toBeDeletedSampleMock->expects($this->once())->method('getId')->willReturn($toBeDeletedSampleId);

        $this->savedProductMock->expects($this->any())->method('getSku')->willReturn($productSku);
        $this->savedProductMock->expects($this->exactly(2))->method('getExtensionAttributes')
            ->willReturn($this->existingProductExtensionMock);
        $this->existingProductExtensionMock->expects($this->once())
            ->method('getDownloadableProductLinks')
            ->willReturn([$existingLinkMock, $toBeDeletedLinkMock]);
        $this->existingProductExtensionMock->expects($this->once())
            ->method('getDownloadableProductSamples')
            ->willReturn([$existingSampleMock, $toBeDeletedSampleMock]);

        $this->linkRepositoryMock->expects($this->at(0))
            ->method('save')
            ->with($productSku, $updateLinkMock);
        $this->linkRepositoryMock->expects($this->at(1))
            ->method('save')
            ->with($productSku, $newLinkMock);
        $this->linkRepositoryMock->expects($this->at(2))
            ->method('delete')
            ->with($toBeDeletedLinkId);

        $this->sampleRepositoryMock->expects($this->at(0))
            ->method('save')
            ->with($productSku, $updateSampleMock);
        $this->sampleRepositoryMock->expects($this->at(1))
            ->method('save')
            ->with($productSku, $newSampleMock);
        $this->sampleRepositoryMock->expects($this->at(2))
            ->method('delete')
            ->with($toBeDeletedSampleId);

        $newProductMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->disableOriginalConstructor()->getMock();
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku, false, null, true)
            ->willReturn($newProductMock);

        $this->assertEquals(
            $newProductMock,
            $this->model->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }

    /**
     * Input has two links and no samples, one existing and one new
     * Existing product has two links, one will be updated and one will be deleted
     */
    public function testAroundSaveWithOnlyLinks()
    {
        $productSku = "downloadable_product";
        $existingLinkId = '2';
        $toBeDeletedLinkId = '3';

        $this->productMock->expects($this->once())->method('getTypeId')
            ->willReturn(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE);
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $updateLinkMock = $this->getMock('\Magento\Downloadable\Api\Data\LinkInterface');
        $updateLinkMock->expects($this->once())->method('getId')->willReturn($existingLinkId);
        $newLinkMock = $this->getMock('\Magento\Downloadable\Api\Data\LinkInterface');
        $newLinkMock->expects($this->once())->method('getId')->willReturn(null);
        $this->productExtensionMock->expects($this->once())
            ->method('getDownloadableProductLinks')
            ->willReturn([$newLinkMock, $updateLinkMock]);

        $this->productExtensionMock->expects($this->once())
            ->method('getDownloadableProductSamples')
            ->willReturn(null);

        $existingLinkMock = $this->getMock('\Magento\Downloadable\Api\Data\LinkInterface');
        $existingLinkMock->expects($this->once())->method('getId')->willReturn($existingLinkId);
        $toBeDeletedLinkMock = $this->getMock('\Magento\Downloadable\Api\Data\LinkInterface');
        $toBeDeletedLinkMock->expects($this->once())->method('getId')->willReturn($toBeDeletedLinkId);

        $this->savedProductMock->expects($this->any())->method('getSku')->willReturn($productSku);
        $this->savedProductMock->expects($this->once())->method('getExtensionAttributes')
            ->willReturn($this->existingProductExtensionMock);
        $this->existingProductExtensionMock->expects($this->once())
            ->method('getDownloadableProductLinks')
            ->willReturn([$existingLinkMock, $toBeDeletedLinkMock]);
        $this->existingProductExtensionMock->expects($this->never())
            ->method('getDownloadableProductSamples');

        $this->linkRepositoryMock->expects($this->at(0))
            ->method('save')
            ->with($productSku, $updateLinkMock);
        $this->linkRepositoryMock->expects($this->at(1))
            ->method('save')
            ->with($productSku, $newLinkMock);
        $this->linkRepositoryMock->expects($this->at(2))
            ->method('delete')
            ->with($toBeDeletedLinkId);

        $this->sampleRepositoryMock->expects($this->never())
            ->method('save');

        $newProductMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->disableOriginalConstructor()->getMock();
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku, false, null, true)
            ->willReturn($newProductMock);

        $this->assertEquals(
            $newProductMock,
            $this->model->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }

    /**
     * Input has two samples, one existing and one new
     * Existing product has two samples, one will be updated and one will be deleted
     */
    public function testAroundSaveWithOnlySamples()
    {
        $productSku = "downloadable_product";
        $existingSampleId = '5';
        $toBeDeletedSampleId = '4';

        $this->productMock->expects($this->once())->method('getTypeId')
            ->willReturn(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE);
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getDownloadableProductLinks')
            ->willReturn(null);

        $updateSampleMock = $this->getMock('\Magento\Downloadable\Api\Data\SampleInterface');
        $updateSampleMock->expects($this->once())->method('getId')->willReturn($existingSampleId);
        $newSampleMock = $this->getMock('\Magento\Downloadable\Api\Data\SampleInterface');
        $newSampleMock->expects($this->once())->method('getId')->willReturn(null);
        $this->productExtensionMock->expects($this->once())
            ->method('getDownloadableProductSamples')
            ->willReturn([$updateSampleMock, $newSampleMock]);

        $existingSampleMock = $this->getMock('\Magento\Downloadable\Api\Data\SampleInterface');
        $existingSampleMock->expects($this->once())->method('getId')->willReturn($existingSampleId);
        $toBeDeletedSampleMock = $this->getMock('\Magento\Downloadable\Api\Data\SampleInterface');
        $toBeDeletedSampleMock->expects($this->once())->method('getId')->willReturn($toBeDeletedSampleId);

        $this->savedProductMock->expects($this->any())->method('getSku')->willReturn($productSku);
        $this->savedProductMock->expects($this->once())->method('getExtensionAttributes')
            ->willReturn($this->existingProductExtensionMock);
        $this->existingProductExtensionMock->expects($this->never())
            ->method('getDownloadableProductLinks');
        $this->existingProductExtensionMock->expects($this->once())
            ->method('getDownloadableProductSamples')
            ->willReturn([$existingSampleMock, $toBeDeletedSampleMock]);

        $this->linkRepositoryMock->expects($this->never())
            ->method('save');

        $this->sampleRepositoryMock->expects($this->at(0))
            ->method('save')
            ->with($productSku, $updateSampleMock);
        $this->sampleRepositoryMock->expects($this->at(1))
            ->method('save')
            ->with($productSku, $newSampleMock);
        $this->sampleRepositoryMock->expects($this->at(2))
            ->method('delete')
            ->with($toBeDeletedSampleId);

        $newProductMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->disableOriginalConstructor()->getMock();
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku, false, null, true)
            ->willReturn($newProductMock);

        $this->assertEquals(
            $newProductMock,
            $this->model->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }
}
