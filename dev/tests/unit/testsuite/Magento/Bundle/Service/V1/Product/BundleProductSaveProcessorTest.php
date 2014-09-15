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

namespace Magento\Bundle\Service\V1\Product;

use Magento\TestFramework\Helper\ObjectManager;
use Magento\Catalog\Model\Product\Type as ProductType;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleProductSaveProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Service\V1\Product\BundleProductSaveProcessor
     */
    private $saveProcessor;

    /**
     * @var \Magento\Catalog\Model\ProductRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Service\V1\Data\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productData;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Bundle\Service\V1\Data\Product\Link|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productLink1;

    /**
     * @var \Magento\Bundle\Service\V1\Data\Product\Link|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productLink2;

    /**
     * @var \Magento\Bundle\Service\V1\Data\Product\Link|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productLink3;

    /**
     * @var \Magento\Bundle\Service\V1\Data\Product\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productOption1;

    /**
     * @var \Magento\Bundle\Service\V1\Data\Product\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productOption2;

    /**
     * @var \Magento\Bundle\Service\V1\Product\Link\WriteService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $linkWriteService;

    /**
     * @var \Magento\Bundle\Service\V1\Product\Option\WriteService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionWriteService;

    /**
     * @var \Magento\Bundle\Service\V1\Product\Link\ReadService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $linkReadService;

    /**
     * @var \Magento\Bundle\Service\V1\Product\Option\ReadService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionReadService;

    /**
     * @var \Magento\Catalog\Service\V1\Data\ProductBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productBuilder;

    /**
     * @var \Magento\Framework\Service\Data\Eav\AttributeValue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeValue;

    protected function setup()
    {
        $helper = new ObjectManager($this);

        $this->productRepository = $this->getMockBuilder('Magento\Catalog\Model\ProductRepository')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->productData = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Product')
            ->setMethods(['getSku', 'getTypeId', '__wakeup', 'getCustomAttribute'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getSku', 'getTypeId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->productLink1 = $this->getMockBuilder('Magento\Bundle\Service\V1\Data\Product\Link')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getSku', 'getOptionId'])
            ->getMock();
        $this->productLink1->expects($this->any())
            ->method('getSku')
            ->will($this->returnValue('productLink1Sku'));
        $this->productLink2 = $this->getMockBuilder('Magento\Bundle\Service\V1\Data\Product\Link')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getSku', 'getOptionId'])
            ->getMock();
        $this->productLink2->expects($this->any())
            ->method('getSku')
            ->will($this->returnValue('productLink2Sku'));
        $this->productLink3 = $this->getMockBuilder('Magento\Bundle\Service\V1\Data\Product\Link')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getSku'])
            ->getMock();
        $this->productLink3->expects($this->any())
            ->method('getSku')
            ->will($this->returnValue('productLink3Sku'));

        $this->productOption1 = $this->getMockBuilder('Magento\Bundle\Service\V1\Data\Product\Option')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productOption1->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('productOption1Sku'));
        $this->productOption2 = $this->getMockBuilder('Magento\Bundle\Service\V1\Data\Product\Option')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productOption2->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('productOption2Sku'));

        $this->linkWriteService = $this->getMockBuilder('Magento\Bundle\Service\V1\Product\Link\WriteService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionWriteService = $this->getMockBuilder('Magento\Bundle\Service\V1\Product\Option\WriteService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->linkReadService = $this->getMockBuilder('Magento\Bundle\Service\V1\Product\Link\ReadService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionReadService = $this->getMockBuilder('Magento\Bundle\Service\V1\Product\Option\ReadService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->productBuilder = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\ProductBuilder')
            ->setMethods(['setCustomAttribute'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeValue = $this->getMockBuilder('Magento\Framework\Service\Data\Eav\AttributeValue')
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->saveProcessor = $helper->getObject(
            'Magento\Bundle\Service\V1\Product\BundleProductSaveProcessor',
            [
                'linkWriteService' => $this->linkWriteService,
                'optionWriteService' => $this->optionWriteService,
                'linkReadService' => $this->linkReadService,
                'optionReadService' => $this->optionReadService,
                'productRepository' => $this->productRepository,
            ]
        );
    }

    public function testCreate()
    {
        $productSku = 'sku';
        $productOptions = [$this->productOption1, $this->productOption2];

        $this->product->expects($this->any())
            ->method('getSku')
            ->will($this->returnValue($productSku));
        $this->product->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue(ProductType::TYPE_BUNDLE));

        $this->productData->expects($this->once())
            ->method('getCustomAttribute')
            ->with('bundle_product_options')
            ->will($this->returnValue($this->attributeValue));
        $this->attributeValue->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($productOptions));

        $this->optionWriteService->expects($this->at(0))
            ->method('add')
            ->with($productSku, $this->productOption1)
            ->will($this->returnValue(1));
        $this->optionWriteService->expects($this->at(1))
            ->method('add')
            ->with($productSku, $this->productOption2)
            ->will($this->returnValue(2));

        $this->assertEquals($productSku, $this->saveProcessor->create($this->product, $this->productData));
        $this->assertEquals($productSku, $this->saveProcessor->afterCreate($this->product, $this->productData));
    }

    public function testUpdate()
    {
        $product1Sku = 'sku1';
        $productOption1Sku = 'productOption1Sku';
        $productOption2Sku = 'productOption2Sku';
        $product1Options = [$this->productOption1, $this->productOption2];

        $product2Options = [$this->productOption1];

        $this->productRepository->expects($this->once())
            ->method('get')
            ->with($product1Sku, true)
            ->will($this->returnValue($this->product));
        $this->product->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue(ProductType::TYPE_BUNDLE));

        $this->optionReadService->expects($this->once())
            ->method('getList')
            ->with($product1Sku)
            ->will($this->returnValue($product1Options));
        $this->productData->expects($this->once())
            ->method('getCustomAttribute')
            ->with('bundle_product_options')
            ->will($this->returnValue($this->attributeValue));
        $this->attributeValue->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($product2Options));
        $this->productOption1->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($productOption1Sku));
        $this->productOption2->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($productOption2Sku));
        $this->optionWriteService->expects($this->once())
            ->method('remove')
            ->with($product1Sku, $productOption2Sku)
            ->will($this->returnValue(1));

        $this->assertEquals($product1Sku, $this->saveProcessor->update($product1Sku, $this->productData));
    }

    public function testDelete()
    {
        $productSku = 'sku1';
        $productOptions = [$this->productOption1];
        $productOption1Sku = 'productOption1Sku';

        $this->productData->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue(ProductType::TYPE_BUNDLE));
        $this->productData->expects($this->once())
            ->method('getSku')
            ->will($this->returnValue($productSku));
        $this->productData->expects($this->once())
            ->method('getCustomAttribute')
            ->with('bundle_product_options')
            ->will($this->returnValue($this->attributeValue));
        $this->attributeValue->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($productOptions));
        $this->optionWriteService->expects($this->once())
            ->method('remove')
            ->with($productSku, $productOption1Sku)
            ->will($this->returnValue(1));
        $this->saveProcessor->delete($this->productData);
    }
}
