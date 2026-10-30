<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Controller\Adminhtml\Product\Attribute\Update\InputType;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Update\AbstractUpdateAttributeTest;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test cases related to update attribute with input type fixed product tax.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class FixedProductTaxTest extends AbstractUpdateAttributeTest
{
    /**
     * Test update attribute.
     *
     * @magentoDataFixture Magento/Weee/_files/fixed_product_attribute.php
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    #[DataProvider('getUpdateProvider')]
    public function testUpdateAttribute(array $postData, array $expectedData): void
    {
        $this->updateAttributeUsingData('fixed_product_attribute', $postData);
        $this->assertUpdateAttributeProcess('fixed_product_attribute', $postData, $expectedData);
    }

    /**
     * Test update attribute with error.
     *
     * @magentoDataFixture Magento/Weee/_files/fixed_product_attribute.php
     *
     * @param array $postData
     * @param string $errorMessage
     * @return void
     */
    #[DataProvider('getUpdateProviderWithErrorMessage')]
    public function testUpdateAttributeWithError(array $postData, string $errorMessage): void
    {
        $this->updateAttributeUsingData('fixed_product_attribute', $postData);
        $this->assertErrorSessionMessages($errorMessage);
    }

    /**
     * Test update attribute frontend labels on stores.
     *
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/Weee/_files/fixed_product_attribute.php
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    #[DataProvider('getUpdateFrontendLabelsProvider')]
    public function testUpdateFrontendLabelOnStores(array $postData, array $expectedData): void
    {
        $this->processUpdateFrontendLabelOnStores('fixed_product_attribute', $postData, $expectedData);
    }

    /**
     * @return array
     */
    public static function getUpdateProvider(): array
    {
        return \Magento\TestFramework\Weee\Model\Attribute\DataProvider\FixedProductTax::getUpdateProvider();
    }

    /**
     * @return array
     */
    public static function getUpdateProviderWithErrorMessage(): array
    {
        return \Magento\TestFramework\Weee\Model\Attribute\DataProvider\FixedProductTax::getUpdateProviderWithErrorMessage();
    }

    /**
     * @return array
     */
    public static function getUpdateFrontendLabelsProvider(): array
    {
        return \Magento\TestFramework\Weee\Model\Attribute\DataProvider\FixedProductTax::getUpdateFrontendLabelsProvider();
    }
}
