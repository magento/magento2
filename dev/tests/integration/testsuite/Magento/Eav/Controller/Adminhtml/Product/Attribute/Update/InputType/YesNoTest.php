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
 * Test cases related to update attribute with yes/no input type.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class YesNoTest extends AbstractUpdateAttributeTest
{
    /**
     * Test update attribute.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_boolean_attribute.php
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\Eav\Model\Attribute\DataProvider\YesNo::class, 'getUpdateProvider')]
    public function testUpdateAttribute(array $postData, array $expectedData): void
    {
        $this->updateAttributeUsingData('boolean_attribute', $postData);
        $this->assertUpdateAttributeProcess('boolean_attribute', $postData, $expectedData);
    }

    /**
     * Test update attribute with error.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_boolean_attribute.php
     *
     * @param array $postData
     * @param string $errorMessage
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\Eav\Model\Attribute\DataProvider\YesNo::class, 'getUpdateProviderWithErrorMessage')]
    public function testUpdateAttributeWithError(array $postData, string $errorMessage): void
    {
        $this->updateAttributeUsingData('boolean_attribute', $postData);
        $this->assertErrorSessionMessages($errorMessage);
    }

    /**
     * Test update attribute frontend labels on stores.
     *
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/Catalog/_files/product_boolean_attribute.php
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\Eav\Model\Attribute\DataProvider\YesNo::class, 'getUpdateFrontendLabelsProvider')]
    public function testUpdateFrontendLabelOnStores(array $postData, array $expectedData): void
    {
        $this->processUpdateFrontendLabelOnStores('boolean_attribute', $postData, $expectedData);
    }
}
