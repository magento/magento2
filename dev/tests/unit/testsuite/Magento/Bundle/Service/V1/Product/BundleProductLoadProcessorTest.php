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

class BundleProductLoadProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Service\V1\Product\BundleProductLoadProcessor
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\ProductRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Bundle\Service\V1\Product\Option\ReadService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionReadService;

    /**
     * @var \Magento\Catalog\Service\V1\Data\ProductBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productBuilder;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->productRepository = $this->getMockBuilder('Magento\Catalog\Model\ProductRepository')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getSku', 'getTypeId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionReadService = $this->getMockBuilder('Magento\Bundle\Service\V1\Product\Option\ReadService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->productBuilder = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\ProductBuilder')
            ->setMethods(['setCustomAttribute'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $helper->getObject(
            'Magento\Bundle\Service\V1\Product\BundleProductLoadProcessor',
            [
                'optionReadService' => $this->optionReadService,
                'productRepository' => $this->productRepository,
            ]
        );
    }

    public function testLoadNotBundleProduct()
    {
        $productId = 'test_id';

        $this->productRepository->expects($this->once())
            ->method('get')
            ->with($productId)
            ->will($this->returnValue($this->product));
        $this->product->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE));

        $this->model->load($productId, $this->productBuilder);
    }

    public function testLoadBundleProduct()
    {
        $productId = 'test_id';
        $productSku = 'test_sku';

        $this->productRepository->expects($this->once())
            ->method('get')
            ->with($productId)
            ->will($this->returnValue($this->product));
        $this->product->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));
        $this->product->expects($this->once())
            ->method('getSku')
            ->will($this->returnValue($productSku));

        $optionCustomAttributeValue = ['a', 'b'];
        $this->optionReadService->expects($this->once())
            ->method('getList')
            ->with($productSku)
            ->will($this->returnValue($optionCustomAttributeValue));
        $this->productBuilder->expects($this->at(0))
            ->method('setCustomAttribute')
            ->with('bundle_product_options', $optionCustomAttributeValue);

        $this->model->load($productId, $this->productBuilder);
    }
}
