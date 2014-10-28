<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GoogleOptimizer\Helper;

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
        $this->_codeModelMock = $this->getMock('Magento\GoogleOptimizer\Model\Code', array(), array(), '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_helper = $objectManagerHelper->getObject(
            'Magento\GoogleOptimizer\Helper\Code',
            array('code' => $this->_codeModelMock)
        );
    }

    public function testLoadingCodeForCategoryEntity()
    {
        $categoryMock = $this->getMock('Magento\Catalog\Model\Category', array(), array(), '', false);

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
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);

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
        $pageMock = $this->getMock('Magento\Cms\Model\Page', array(), array(), '', false);

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
        $entity = $this->getMock('Magento\Cms\Model\Block', array(), array(), '', false);

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
        $entity = $this->getMock('Magento\Cms\Model\Block', array(), array(), '', false);

        $entityId = 0;

        $entity->expects($this->exactly(1))->method('getId')->will($this->returnValue($entityId));
        $this->_codeModelMock->expects($this->never())->method('loadByEntityIdAndType');

        $this->assertEquals(
            $this->_codeModelMock,
            $this->_helper->getCodeObjectByEntity($entity, \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PAGE)
        );
    }
}
