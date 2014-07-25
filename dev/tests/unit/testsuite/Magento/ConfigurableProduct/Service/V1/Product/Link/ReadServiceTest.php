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

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Model\ProductRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $productRepository;
    /** @var \Magento\Catalog\Service\V1\Data\Converter|\PHPUnit_Framework_MockObject_MockObject */
    protected $productConverter;
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $objectManagerHelper;
    /** @var \Magento\ConfigurableProduct\Service\V1\Product\Link\ReadService */
    protected $object;

    public function setUp()
    {
        $this->productRepository = $this->getMockBuilder('Magento\\Catalog\\Model\\ProductRepository')
            ->disableOriginalConstructor()->getMock();
        $this->productConverter = $this->getMockBuilder('Magento\\Catalog\\Service\\V1\\Data\\Converter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->object = $this->objectManagerHelper->getObject(
            'Magento\\ConfigurableProduct\\Service\\V1\\Product\\Link\\ReadService',
            ['productRepository' => $this->productRepository, 'productConverter' => $this->productConverter]
        );
    }

    public function testGetChildren()
    {
        $productId = 'sadasd';

        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeInstance = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
            ->disableOriginalConstructor()
            ->getMock();

        $product->expects($this->any())->method('getTypeId')->will(
            $this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );

        $product->expects($this->any())->method('getTypeInstance')->will(
            $this->returnValue($productTypeInstance)
        );

        $productTypeInstance->expects($this->once())->method('setStoreFilter')
            ->with(null, $product);

        $childProduct = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeInstance->expects($this->any())->method('getUsedProducts')
            ->with($product)->will($this->returnValue([$childProduct]));

        $this->productRepository->expects($this->any())
            ->method('get')->with($productId)
            ->will(
                $this->returnValue($product)
            );

        $productDto = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $this->productConverter->expects($this->any())
            ->method('createProductDataFromModel')->with($childProduct)
            ->will(
                $this->returnValue($productDto)
            );

        $products = $this->object->getChildren($productId);
        $this->assertCount(1, $products);
        $this->assertEquals($productDto, $products[0]);
    }

    public function testGetChildrenException()
    {
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $product->expects($this->any())->method('getTypeId')->will(
            $this->returnValue('same')
        );

        $this->productRepository->expects($this->any())
            ->method('get')->with('sd')
            ->will(
                $this->returnValue($product)
            );
        $this->assertCount(0, $this->object->getChildren('sd'));
    }
}