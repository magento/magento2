<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

class CategoryTest extends \PHPUnit_Framework_TestCase
{
    public function testAfterLoad()
    {
        $categoryIds = [1, 2, 3, 4, 5];

        $product = $this->getMock('Magento\Framework\DataObject', ['getCategoryIds', 'setData']);
        $product->expects($this->once())->method('getCategoryIds')->will($this->returnValue($categoryIds));

        $product->expects($this->once())->method('setData')->with('category_ids', $categoryIds);

        $categoryAttribute = $this->getMock('Magento\Framework\DataObject', ['getAttributeCode']);
        $categoryAttribute->expects(
            $this->once()
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue('category_ids')
        );

        $model = new \Magento\Catalog\Model\Product\Attribute\Backend\Category();
        $model->setAttribute($categoryAttribute);

        $model->afterLoad($product);
    }
}
