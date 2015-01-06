<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Product;

use Magento\TestFramework\Helper\ObjectManager;

class OptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
    {
        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Catalog\Model\Product\Option');
        $this->model->setProduct($this->productMock);
    }

    public function testGetProductSku()
    {
        $productSku = 'product-sku';
        $this->productMock->expects($this->once())->method('getSku')->willReturn($productSku);
        $this->assertEquals($productSku, $this->model->getProductSku());
    }
}
