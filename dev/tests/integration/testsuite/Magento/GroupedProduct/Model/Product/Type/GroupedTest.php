<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GroupedProduct\Model\Product\Type;

class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_productType;

    protected function setUp()
    {
        $this->_productType = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Product\Type'
        );
    }

    public function testFactory()
    {
        $product = new \Magento\Framework\Object();
        $product->setTypeId(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE);
        $type = $this->_productType->factory($product);
        $this->assertInstanceOf('\Magento\GroupedProduct\Model\Product\Type\Grouped', $type);
    }
}
