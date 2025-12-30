<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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

        $product = $this->createPartialMock(DataObject::class, []);
        $product->setCategoryIds($categoryIds);

        $categoryAttribute = $this->createPartialMock(DataObject::class, []);
        $categoryAttribute->setAttributeCode('category_ids');

        $model = new Category();
        $model->setAttribute($categoryAttribute);

        $model->afterLoad($product);
        
        // Verify that the product data was set correctly
        $this->assertEquals($categoryIds, $product->getData('category_ids'));
    }
}
