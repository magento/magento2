<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Controller\Adminhtml\Product\Attribute\Save\InputType;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save\InputType\AbstractSaveAttributeTest;

/**
 * Test cases related to create attribute with input type dropdown.
 *
 * @magentoDbIsolation enabled
 */
class DropDownTest extends AbstractSaveAttributeTest
{
    /**
     * Test create attribute and compare attribute data and input data.
     *
     * @dataProvider \Magento\TestFramework\Eav\Model\Attribute\DataProvider\DropDown::getAttributeDataWithCheckArray()
     *
     * @param array $attributePostData
     * @param array $checkArray
     * @return void
     */
    public function testCreateAttribute(array $attributePostData, array $checkArray): void
    {
        $this->createAttributeUsingDataAndAssert($attributePostData, $checkArray);
    }

    /**
     * Test create attribute with error.
     *
     * @dataProvider \Magento\TestFramework\Eav\Model\Attribute\DataProvider\DropDown::getAttributeDataWithErrorMessage()
     *
     * @param array $attributePostData
     * @param string $errorMessage
     * @return void
     */
    public function testCreateAttributeWithError(array $attributePostData, string $errorMessage): void
    {
        $this->createAttributeUsingDataWithErrorAndAssert($attributePostData, $errorMessage);
    }
}
