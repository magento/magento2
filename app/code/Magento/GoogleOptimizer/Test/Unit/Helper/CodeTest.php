<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Test\Unit\Helper;

class CodeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_codeModelMock;

    /**
     * @var \Magento\GoogleOptimizer\Helper\Code
     */
    protected $_helper;

    protected function setUp(): void
    {
        $this->_codeModelMock = $this->createMock(\Magento\GoogleOptimizer\Model\Code::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_helper = $objectManagerHelper->getObject(
            \Magento\GoogleOptimizer\Helper\Code::class,
            ['code' => $this->_codeModelMock]
        );
    }

    public function testLoadingCodeForCategoryEntity()
    {
        $categoryMock = $this->createMock(\Magento\Catalog\Model\Category::class);

        $categoryId = 1;
        $storeId = 1;

        $categoryMock->expects($this->exactly(2))->method('getId')->willReturn($categoryId);
        $categoryMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $this->_codeModelMock->expects(
            $this->once()
        )->method(
            'loadByEntityIdAndType'
        )->with(
            $categoryId,
            \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_CATEGORY,
            $storeId
        );

        $this->assertEquals(
            $this->_codeModelMock,
            $this->_helper->getCodeObjectByEntity(
                $categoryMock,
                \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_CATEGORY
            )
        );
    }

    public function testLoadingCodeForProductEntity()
    {
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);

        $categoryId = 1;
        $storeId = 1;

        $productMock->expects($this->exactly(2))->method('getId')->willReturn($categoryId);
        $productMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $this->_codeModelMock->expects(
            $this->once()
        )->method(
            'loadByEntityIdAndType'
        )->with(
            $categoryId,
            \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PRODUCT,
            $storeId
        );

        $this->assertEquals(
            $this->_codeModelMock,
            $this->_helper->getCodeObjectByEntity(
                $productMock,
                \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PRODUCT
            )
        );
    }

    public function testLoadingCodeForPageEntity()
    {
        $pageMock = $this->createMock(\Magento\Cms\Model\Page::class);

        $categoryId = 1;

        $pageMock->expects($this->exactly(2))->method('getId')->willReturn($categoryId);
        $this->_codeModelMock->expects(
            $this->once()
        )->method(
            'loadByEntityIdAndType'
        )->with(
            $categoryId,
            \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PAGE
        );

        $this->assertEquals(
            $this->_codeModelMock,
            $this->_helper->getCodeObjectByEntity($pageMock, \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PAGE)
        );
    }

    /**
     */
    public function testExceptionNotValidEntityType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The model class is not valid');

        $entity = $this->createMock(\Magento\Cms\Model\Block::class);

        $entityId = 1;

        $entity->expects($this->exactly(2))->method('getId')->willReturn($entityId);
        $this->_codeModelMock->expects($this->never())->method('loadByEntityIdAndType');

        $this->assertEquals(
            $this->_codeModelMock,
            $this->_helper->getCodeObjectByEntity($entity, \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PAGE)
        );
    }

    /**
     */
    public function testExceptionEmptyEntity()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The model is empty');

        $entity = $this->createMock(\Magento\Cms\Model\Block::class);

        $entityId = 0;

        $entity->expects($this->exactly(1))->method('getId')->willReturn($entityId);
        $this->_codeModelMock->expects($this->never())->method('loadByEntityIdAndType');

        $this->assertEquals(
            $this->_codeModelMock,
            $this->_helper->getCodeObjectByEntity($entity, \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PAGE)
        );
    }
}
