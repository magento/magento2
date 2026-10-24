<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Controller\Adminhtml\Product\Attribute\Save\InputType;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save\AbstractSaveAttributeTest;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test cases related to create attribute with input type fixed product tax.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class FixedProductTaxTest extends AbstractSaveAttributeTest
{
    /**
     * Test create attribute and compare attribute data and input data.
     *
     * @param array $attributePostData
     * @param array $checkArray
     * @return void
     */
    #[DataProvider('getAttributeDataWithCheckArray')]
    public function testCreateAttribute(array $attributePostData, array $checkArray): void
    {
        $this->createAttributeUsingDataAndAssert($attributePostData, $checkArray);
    }

    /**
     * Test create attribute with error.
     *
     * @param array $attributePostData
     * @param string $errorMessage
     * @return void
     */
    #[DataProvider('getAttributeDataWithErrorMessage')]
    public function testCreateAttributeWithError(array $attributePostData, string $errorMessage): void
    {
        $this->createAttributeUsingDataWithErrorAndAssert($attributePostData, $errorMessage);
    }

    /**
     * @return array
     */
    public static function getAttributeDataWithCheckArray(): array
    {
        return \Magento\TestFramework\Weee\Model\Attribute\DataProvider\FixedProductTax::getAttributeDataWithCheckArray();
    }

    /**
     * @return array
     */
    public static function getAttributeDataWithErrorMessage(): array
    {
        return \Magento\TestFramework\Weee\Model\Attribute\DataProvider\FixedProductTax::getAttributeDataWithErrorMessage();
    }
}
