<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Test\Unit\Helper;

class CodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_codeModelMock;

    /**
     * @var \Magento\GoogleOptimizer\Helper\Code
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_codeModelMock = $this->getMock('Magento\GoogleOptimizer\Model\Code', [], [], '', false);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_helper = $objectManagerHelper->getObject(
            'Magento\GoogleOptimizer\Helper\Code',
            ['code' => $this->_codeModelMock]
        );
    }

    public function testLoadingCodeForCategoryEntity()
    {
        $categoryMock = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);

        $categoryId = 1;
        $storeId = 1;

        $categoryMock->expects($this->exactly(2))->method('getId')->will($this->returnValue($categoryId));
        $categoryMock->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));
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
        $productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);

        $categoryId = 1;
        $storeId = 1;

        $productMock->expects($this->exactly(2))->method('getId')->will($this->returnValue($categoryId));
        $productMock->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));
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
        $pageMock = $this->getMock('Magento\Cms\Model\Page', [], [], '', false);

        $categoryId = 1;

        $pageMock->expects($this->exactly(2))->method('getId')->will($this->returnValue($categoryId));
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The model class is not valid
     */
    public function testExceptionNotValidEntityType()
    {
        $entity = $this->getMock('Magento\Cms\Model\Block', [], [], '', false);

        $entityId = 1;

        $entity->expects($this->exactly(2))->method('getId')->will($this->returnValue($entityId));
        $this->_codeModelMock->expects($this->never())->method('loadByEntityIdAndType');

        $this->assertEquals(
            $this->_codeModelMock,
            $this->_helper->getCodeObjectByEntity($entity, \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PAGE)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The model is empty
     */
    public function testExceptionEmptyEntity()
    {
        $entity = $this->getMock('Magento\Cms\Model\Block', [], [], '', false);

        $entityId = 0;

        $entity->expects($this->exactly(1))->method('getId')->will($this->returnValue($entityId));
        $this->_codeModelMock->expects($this->never())->method('loadByEntityIdAndType');

        $this->assertEquals(
            $this->_codeModelMock,
            $this->_helper->getCodeObjectByEntity($entity, \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PAGE)
        );
    }
}
