<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product\Attribute\Backend\Category;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testAfterLoad()
    {
        $categoryIds = [1, 2, 3, 4, 5];

        $product = $this->createPartialMock(DataObject::class, ['getCategoryIds', 'setData']);
        $product->expects($this->once())->method('getCategoryIds')->will($this->returnValue($categoryIds));

        $product->expects($this->once())->method('setData')->with('category_ids', $categoryIds);

        $categoryAttribute = $this->createPartialMock(DataObject::class, ['getAttributeCode']);
        $categoryAttribute->expects(
            $this->once()
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue('category_ids')
        );

        $model = new Category();
        $model->setAttribute($categoryAttribute);

        $model->afterLoad($product);
    }
}
