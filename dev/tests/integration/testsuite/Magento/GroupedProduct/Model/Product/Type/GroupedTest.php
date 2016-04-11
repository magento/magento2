<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $product = new \Magento\Framework\DataObject();
        $product->setTypeId(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE);
        $type = $this->_productType->factory($product);
        $this->assertInstanceOf('\Magento\GroupedProduct\Model\Product\Type\Grouped', $type);
    }
}
