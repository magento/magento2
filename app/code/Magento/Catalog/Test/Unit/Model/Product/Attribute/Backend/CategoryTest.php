<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product\Attribute\Backend\Category;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testAfterLoad()
    {
        $categoryIds = [1, 2, 3, 4, 5];

        $product = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getCategoryIds'])
            ->onlyMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('getCategoryIds')->willReturn($categoryIds);

        $product->expects($this->once())->method('setData')->with('category_ids', $categoryIds);

        $categoryAttribute = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $categoryAttribute->expects(
            $this->once()
        )->method(
            'getAttributeCode'
        )->willReturn(
            'category_ids'
        );

        $model = new Category();
        $model->setAttribute($categoryAttribute);

        $model->afterLoad($product);
    }
}
