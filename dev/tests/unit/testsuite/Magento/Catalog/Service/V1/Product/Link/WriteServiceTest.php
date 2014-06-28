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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Service\V1\Product\Link;

use \Magento\Framework\Exception\CouldNotSaveException;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WriteService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkInitializerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLoaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataMapperMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->linkInitializerMock = $this->getMock(
            'Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks',
            [],
            [],
            '',
            false
        );

        $this->collectionProviderMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\Link\Data\ProductLink\CollectionProvider',
            [],
            [],
            '',
            false
        );

        $this->productLoaderMock = $this->getMock(
           'Magento\Catalog\Service\V1\Product\ProductLoader',
            [],
            [],
            '',
            false
        );

        $this->productResourceMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Product',
            [],
            [],
            '',
            false
        );

        $this->dataMapperMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\Link\Data\ProductLink\DataMapperInterface',
            [],
            [],
            '',
            false
        );
        $this->service = $helper->getObject('Magento\Catalog\Service\V1\Product\Link\WriteService',
            [
                'linkInitializer' => $this->linkInitializerMock,
                'entityCollectionProvider' => $this->collectionProviderMock,
                'productLoader' => $this->productLoaderMock,
                'productResource' => $this->productResourceMock,
                'dataMapper' => $this->dataMapperMock
            ]
        );
    }

    public function testSaveLinks()
    {
        $assignedProductMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);

        $this->productLoaderMock
            ->expects($this->once())
            ->method('load')
            ->with('product_sku')
            ->will($this->returnValue($productMock));
        $assignedProductMock->expects($this->any())->method('getSku')->will($this->returnValue('assigned_sku'));
        $this->productResourceMock
            ->expects($this->once())
            ->method('getProductsIdsBySkus')
            ->with(array('assigned_sku'))
           ->will($this->returnValue(['assigned_sku' => 1]));
        $this->dataMapperMock->expects($this->once())->method('map')->with([1 => ['product_id' => 1]]);
        $productMock->expects($this->once())->method('save');
        $this->assertTrue($this->service->assign('product_sku', array($assignedProductMock), 'product_type'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Product with SKU "assigned_sku" does not exist
     */
    public function testSaveLinkWithNotExistingSku()
    {
        $assignedProductMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);

        $this->productLoaderMock
            ->expects($this->once())
            ->method('load')
            ->with('product_sku')
            ->will($this->returnValue($productMock));
        $assignedProductMock->expects($this->any())->method('getSku')->will($this->returnValue('assigned_sku'));
        $this->productResourceMock
            ->expects($this->once())
            ->method('getProductsIdsBySkus')
            ->with(array('assigned_sku'))
            ->will($this->returnValue(['some_sku' => 1]));
        $productMock->expects($this->never())->method('save');
        $this->service->assign('product_sku', array($assignedProductMock), 'product_type');
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Invalid data provided for linked products
     */
    public function testSaveLinkWithInvalidData()
    {
        $assignedProductMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);

        $this->productLoaderMock
            ->expects($this->once())
            ->method('load')
            ->with('product_sku')
            ->will($this->returnValue($productMock));
        $assignedProductMock->expects($this->any())->method('getSku')->will($this->returnValue('assigned_sku'));
        $this->productResourceMock
            ->expects($this->once())
            ->method('getProductsIdsBySkus')
            ->with(array('assigned_sku'))
            ->will($this->returnValue(['assigned_sku' => 1]));
        $this->dataMapperMock->expects($this->once())->method('map')->with([1 => ['product_id' => 1]]);
        $productMock
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException(new CouldNotSaveException('Invalid data provided for linked products')));
        $this->service->assign('product_sku', array($assignedProductMock), 'product_type');
    }

    public function testSuccessUpdate()
    {
        $linkedEntityMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\Link\Data\ProductLink',
            [],
            [],
            '',
            false
        );
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $linkedProductMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $valueMap = [
            ['product_sku', $productMock],
            ['linked_product_sku', $linkedProductMock]
        ];
        $linkedEntityMock->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $linkedProductMock->expects($this->exactly(3))->method('getId')->will($this->returnValue(10));
        $this->productLoaderMock
            ->expects($this->any())
            ->method('load')
            ->will($this->returnValueMap($valueMap));
        $this->collectionProviderMock
            ->expects($this->once())
            ->method('getCollection')
            ->with($productMock, 'product_type')
            ->will($this->returnValue([10 => 1]));
        $this->dataMapperMock
            ->expects($this->once())
            ->method('map')
            ->with([10 => ['product_id' => 10]])
            ->will($this->returnValue('mapped_value'));
        $this->linkInitializerMock
            ->expects($this->once())
            ->method('initializeLinks')
            ->with($productMock, ['product_type' => 'mapped_value']);
        $productMock->expects($this->once())->method('save');
        $this->assertTrue($this->service->update('product_sku', $linkedEntityMock, 'product_type'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Product with SKU "linked_product_sku" is not linked to product with SKU product_sku
     */
    public function testUpdateNotLinkedProduct()
    {
        $linkedEntityMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\Link\Data\ProductLink',
            [],
            [],
            '',
            false
        );
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $linkedProductMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $valueMap = [
            ['product_sku', $productMock],
            ['linked_product_sku', $linkedProductMock]
        ];
        $linkedEntityMock->expects($this->exactly(2))->method('getSku')->will($this->returnValue('linked_product_sku'));
        $linkedProductMock->expects($this->exactly(1))->method('getId')->will($this->returnValue(5));
        $this->productLoaderMock
            ->expects($this->any())
            ->method('load')
            ->will($this->returnValueMap($valueMap));
        $this->collectionProviderMock
            ->expects($this->once())
            ->method('getCollection')
            ->with($productMock, 'product_type')
            ->will($this->returnValue([10 => 1]));
        $productMock->expects($this->never())->method('save');
        $this->service->update('product_sku', $linkedEntityMock, 'product_type');
    }

    public function testSuccessRemove()
    {
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $linkedProductMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $valueMap = [
            ['product_sku', $productMock],
            ['linked_product_sku', $linkedProductMock]
        ];
        $linkedProductMock->expects($this->exactly(2))->method('getId')->will($this->returnValue(10));
        $this->productLoaderMock
            ->expects($this->any())
            ->method('load')
            ->will($this->returnValueMap($valueMap));
        $this->collectionProviderMock
            ->expects($this->once())
            ->method('getCollection')
            ->with($productMock, 'product_type')
            ->will($this->returnValue([10 => 1]));
        $this->dataMapperMock
            ->expects($this->once())
            ->method('map')
            ->with([])
            ->will($this->returnValue('mapped_value'));
        $this->linkInitializerMock
            ->expects($this->once())
            ->method('initializeLinks')
            ->with($productMock, ['product_type' => 'mapped_value']);
        $productMock->expects($this->once())->method('save');
        $this->assertTrue($this->service->remove('product_sku', 'linked_product_sku', 'product_type'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Product with SKU linked_product_sku is not linked to product with SKU product_sku
     */
    public function testRemoveNotLinkedProduct()
    {
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $linkedProductMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $valueMap = [
            ['product_sku', $productMock],
            ['linked_product_sku', $linkedProductMock]
        ];
        $linkedProductMock->expects($this->exactly(1))->method('getId')->will($this->returnValue(5));
        $this->productLoaderMock
            ->expects($this->any())
            ->method('load')
            ->will($this->returnValueMap($valueMap));
        $this->collectionProviderMock
            ->expects($this->once())
            ->method('getCollection')
            ->with($productMock, 'product_type')
            ->will($this->returnValue([10 => 1]));
        $productMock->expects($this->never())->method('save');
        $this->service->remove('product_sku', 'linked_product_sku', 'product_type');
    }
}
