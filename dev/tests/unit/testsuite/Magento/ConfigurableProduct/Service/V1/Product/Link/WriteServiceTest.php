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
namespace Magento\ConfigurableProduct\Service\V1\Product\Link;


use Magento\TestFramework\Helper\ObjectManager;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Service\V1\Product\Link\WriteService
     */
    private $service;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productType;

    /** @var \Magento\Catalog\Model\ProductRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $productRepository;

    /** @var Configurable|\PHPUnit_Framework_MockObject_MockObject */
    protected $configurableType;

    protected function setUp()
    {
        $this->productType = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
            ->setMethods(['getUsedProducts'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getTypeInstance', 'save', 'getTypeId', 'addData', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->product
            ->expects($this->any())
            ->method('getTypeInstance')
            ->will($this->returnValue($this->productType));

        $this->productRepository = $this->getMockBuilder('Magento\Catalog\Model\ProductRepository')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurableType =
            $this->getMockBuilder('Magento\\ConfigurableProduct\\Model\\Resource\\Product\\Type\\Configurable')
                ->disableOriginalConstructor()->getMock();

        $this->service = (new ObjectManager($this))->getObject(
            'Magento\ConfigurableProduct\Service\V1\Product\Link\WriteService',
            [
                'productRepository' => $this->productRepository,
                'configurableType' => $this->configurableType
            ]
        );
    }

    public function testRemoveChild()
    {
        $productSku = 'configurable';
        $childSku = 'simple_10';

        $this->product
            ->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE));
        $this->productRepository->expects($this->any())->method('get')->will($this->returnValue($this->product));

        $option = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['getSku', 'getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->any())->method('getSku')->will($this->returnValue($childSku));
        $option->expects($this->any())->method('getId')->will($this->returnValue(10));
        $this->productType->expects($this->once())->method('getUsedProducts')
            ->will($this->returnValue([$option]));

        $this->product->expects($this->once())->method('addData')->with(['associated_product_ids' => array()]);
        $this->product->expects($this->once())->method('save');
        $this->assertTrue($this->service->removeChild($productSku, $childSku));
    }

    /**
     * @expectedException \Magento\Webapi\Exception
     * @expectedExceptionCode 403
     */
    public function testRemoveChildForbidden()
    {
        $productSku = 'configurable';
        $childSku = 'simple_10';

        $this->product
            ->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE));
        $this->productRepository->expects($this->any())->method('get')->will($this->returnValue($this->product));
        $this->service->removeChild($productSku, $childSku);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRemoveChildInvalidChildSku()
    {
        $productSku = 'configurable';
        $childSku = 'simple_10';

        $this->product
            ->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE));
        $this->productRepository->expects($this->any())->method('get')->will($this->returnValue($this->product));

        $option = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['getSku', 'getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->any())->method('getSku')->will($this->returnValue($childSku . '_invalid'));
        $option->expects($this->any())->method('getId')->will($this->returnValue(10));
        $this->productType->expects($this->once())->method('getUsedProducts')
            ->will($this->returnValue([$option]));

        $this->service->removeChild($productSku, $childSku);
    }

    public function testAddChild()
    {
        $productSku = 'configurable-sku';
        $childSku = 'simple-sku';

        $configurable = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $configurable->expects($this->any())->method('getId')->will($this->returnValue(666));

        $simplee = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $simplee->expects($this->any())->method('getId')->will($this->returnValue(999));

        $this->productRepository->expects($this->at(0))->method('get')->with($productSku)->will(
            $this->returnValue($configurable)
        );

        $this->productRepository->expects($this->at(1))->method('get')->with($childSku)->will(
            $this->returnValue($simplee)
        );

        $this->configurableType->expects($this->once())->method('getChildrenIds')->with(666)
            ->will(
                $this->returnValue([0 => [1, 2, 3]])
            );
        $configurable->expects($this->once())->method('__call')->with('setAssociatedProductIds', [[1, 2, 3, 999]]);
        $configurable->expects($this->once())->method('save');

        $this->assertTrue(true, $this->service->addChild($productSku, $childSku));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Product has been already attached
     */
    public function testAddChildStateException()
    {
        $productSku = 'configurable-sku';
        $childSku = 'simple-sku';

        $configurable = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $configurable->expects($this->any())->method('getId')->will($this->returnValue(666));

        $simplee = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $simplee->expects($this->any())->method('getId')->will($this->returnValue(1));

        $this->productRepository->expects($this->at(0))->method('get')->with($productSku)->will(
            $this->returnValue($configurable)
        );

        $this->productRepository->expects($this->at(1))->method('get')->with($childSku)->will(
            $this->returnValue($simplee)
        );

        $this->configurableType->expects($this->once())->method('getChildrenIds')->with(666)
            ->will(
                $this->returnValue([0 => [1, 2, 3]])
            );

        $this->assertTrue(true, $this->service->addChild($productSku, $childSku));
    }
}
