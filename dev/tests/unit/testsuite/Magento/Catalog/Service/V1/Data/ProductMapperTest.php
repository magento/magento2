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
namespace Magento\Catalog\Service\V1\Data;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ProductMapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var ProductMapper */
    protected $object;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testToModel()
    {
        $productFactory = $this->getMockBuilder('Magento\Catalog\Model\ProductFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Catalog\Service\V1\Data\ProductMapper $productMapper */
        $productMapper = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Service\V1\Data\ProductMapper',
            ['productFactory' => $productFactory]
        );

        $product = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Product')
               ->disableOriginalConstructor()
               ->getMock();
        $product->expects($this->once())->method('__toArray')
            ->will($this->returnValue(['test_code' => 'test_value']));

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $productModel */
        $productModel = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productFactory->expects($this->once())->method('create')->will($this->returnValue($productModel));

        $this->assertEquals($productModel, $productMapper->toModel($product));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Illegal product type
     */
    public function testToModelRuntimeException()
    {
        $productFactory = $this->getMockBuilder('Magento\Catalog\Model\ProductFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Catalog\Service\V1\Data\ProductMapper $productMapper */
        $productMapper = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Service\V1\Data\ProductMapper',
            ['productFactory' => $productFactory]
        );

        $product = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('__toArray')
            ->will($this->returnValue(['test_code' => 'test_value']));

        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $productModel */
        $productModel = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['hasTypeId', 'getTypeId', 'getDefaultAttributeSetId', 'setAttributeSetId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productModel->expects($this->once())->method('hasTypeId')
            ->will($this->returnValue(true));
        $productModel->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(333));
        $productModel->expects($this->once())->method('getDefaultAttributeSetId')
            ->will($this->returnValue(333));
        $productModel->expects($this->once())->method('setAttributeSetId')
            ->with($this->equalTo(333));

        $productFactory->expects($this->once())->method('create')->will($this->returnValue($productModel));

        $productMapper->toModel($product);
    }
}
