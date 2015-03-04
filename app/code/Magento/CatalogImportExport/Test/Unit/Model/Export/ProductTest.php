<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Export;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StubProduct|\Magento\CatalogImportExport\Model\Export\Product
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = new StubProduct();
    }

    protected function tearDown()
    {
        unset($this->_object);
    }

    public function testUpdateDataWithCategoryColumnsNoCategoriesAssigned()
    {
        $dataRow = [];
        $productId = 1;
        $rowCategories = [$productId => []];

        $this->assertTrue($this->_object->updateDataWithCategoryColumns($dataRow, $rowCategories, $productId));
    }
}
