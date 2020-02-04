<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Controller\Adminhtml\Product\Attribute\Update\InputType;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Update\AbstractUpdateAttributeTest;

/**
 * Test cases related to update attribute with input type date.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class DateTimeTest extends AbstractUpdateAttributeTest
{
    /**
     * Test update attribute.
     *
     * @dataProvider \Magento\TestFramework\Eav\Model\Attribute\DataProvider\DateTime::getUpdateProvider
     * @magentoConfigFixture default/general/locale/timezone UTC
     * @magentoDataFixture Magento/Catalog/_files/product_datetime_attribute.php
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    public function testUpdateAttribute(array $postData, array $expectedData): void
    {
        $this->updateAttributeUsingData('datetime_attribute', $postData);
        $this->assertUpdateAttributeProcess('datetime_attribute', $postData, $expectedData);
    }

    /**
     * Test update attribute with error.
     *
     * @dataProvider \Magento\TestFramework\Eav\Model\Attribute\DataProvider\DateTime::getUpdateProviderWithErrorMessage
     * @magentoDataFixture Magento/Catalog/_files/product_datetime_attribute.php
     *
     * @param array $postData
     * @param string $errorMessage
     * @return void
     */
    public function testUpdateAttributeWithError(array $postData, string $errorMessage): void
    {
        $this->updateAttributeUsingData('datetime_attribute', $postData);
        $this->assertErrorSessionMessages($errorMessage);
    }

    /**
     * Test update attribute frontend labels on stores.
     *
     * @dataProvider \Magento\TestFramework\Eav\Model\Attribute\DataProvider\DateTime::getUpdateFrontendLabelsProvider
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/Catalog/_files/product_datetime_attribute.php
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    public function testUpdateFrontendLabelOnStores(array $postData, array $expectedData): void
    {
        $this->processUpdateFrontendLabelOnStores('datetime_attribute', $postData, $expectedData);
    }
}
