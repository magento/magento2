<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Controller\Adminhtml\Product\Attribute\Update\InputType;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Update\AbstractUpdateAttributeTest;
use PHPUnit\Framework\Attributes\DataProviderExternal;

/**
 * Test cases related to update attribute with input type text.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class TextTest extends AbstractUpdateAttributeTest
{
    /**
     * Test update attribute.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_varchar_attribute.php
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\Eav\Model\Attribute\DataProvider\Text::class, 'getUpdateProvider')]
    public function testUpdateAttribute(array $postData, array $expectedData): void
    {
        $this->updateAttributeUsingData('varchar_attribute', $postData);
        $this->assertUpdateAttributeProcess('varchar_attribute', $postData, $expectedData);
    }

    /**
     * Test update attribute with error.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_varchar_attribute.php
     *
     * @param array $postData
     * @param string $errorMessage
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\Eav\Model\Attribute\DataProvider\Text::class, 'getUpdateProviderWithErrorMessage')]
    public function testUpdateAttributeWithError(array $postData, string $errorMessage): void
    {
        $this->updateAttributeUsingData('varchar_attribute', $postData);
        $this->assertErrorSessionMessages($errorMessage);
    }

    /**
     * Test update attribute frontend labels on stores.
     *
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/Catalog/_files/product_varchar_attribute.php
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\Eav\Model\Attribute\DataProvider\Text::class, 'getUpdateFrontendLabelsProvider')]
    public function testUpdateFrontendLabelOnStores(array $postData, array $expectedData): void
    {
        $this->processUpdateFrontendLabelOnStores('varchar_attribute', $postData, $expectedData);
    }
}
