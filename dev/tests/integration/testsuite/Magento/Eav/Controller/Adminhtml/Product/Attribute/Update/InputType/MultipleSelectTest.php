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
 * Test cases related to update attribute with input type multiselect.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class MultipleSelectTest extends AbstractUpdateAttributeTest
{
    /**
     * Test update attribute.
     *
     * @magentoDataFixture Magento/Catalog/_files/multiselect_attribute.php
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\Eav\Model\Attribute\DataProvider\MultipleSelect::class, 'getUpdateProvider')]
    public function testUpdateAttribute(array $postData, array $expectedData): void
    {
        $this->updateAttributeUsingData('multiselect_attribute', $postData);
        $this->assertUpdateAttributeProcess('multiselect_attribute', $postData, $expectedData);
    }

    /**
     * Test update attribute with error.
     *
     * @magentoDataFixture Magento/Catalog/_files/multiselect_attribute.php
     *
     * @param array $postData
     * @param string $errorMessage
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\Eav\Model\Attribute\DataProvider\MultipleSelect::class, 'getUpdateProviderWithErrorMessage')]
    public function testUpdateAttributeWithError(array $postData, string $errorMessage): void
    {
        $this->updateAttributeUsingData('multiselect_attribute', $postData);
        $this->assertErrorSessionMessages($errorMessage);
    }

    /**
     * Test update attribute frontend labels on stores.
     *
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/Catalog/_files/multiselect_attribute.php
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\Eav\Model\Attribute\DataProvider\MultipleSelect::class, 'getUpdateFrontendLabelsProvider')]
    public function testUpdateFrontendLabelOnStores(array $postData, array $expectedData): void
    {
        $this->processUpdateFrontendLabelOnStores('multiselect_attribute', $postData, $expectedData);
    }

    /**
     * Test update attribute options on stores.
     *
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/Catalog/_files/multiselect_attribute.php
     *
     * @param array $postData
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\Eav\Model\Attribute\DataProvider\MultipleSelect::class, 'getUpdateOptionsProvider')]
    public function testUpdateOptionsOnStores(array $postData): void
    {
        $this->processUpdateOptionsOnStores('multiselect_attribute', $postData);
    }
}
