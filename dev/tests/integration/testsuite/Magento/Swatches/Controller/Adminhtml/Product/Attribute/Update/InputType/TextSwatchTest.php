<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Controller\Adminhtml\Product\Attribute\Update\InputType;

use Magento\Swatches\Controller\Adminhtml\Product\Attribute\Update\AbstractUpdateSwatchAttributeTest;
use Magento\Swatches\Model\Swatch;

/**
 * Test cases related to update attribute with input type text swatch.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class TextSwatchTest extends AbstractUpdateSwatchAttributeTest
{
    /**
     * Test update attribute.
     *
     * @dataProvider \Magento\TestFramework\Swatches\Model\Attribute\DataProvider\TextSwatch::getUpdateProvider
     * @magentoDataFixture Magento/Swatches/_files/product_text_swatch_attribute.php
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    public function testUpdateAttribute(array $postData, array $expectedData): void
    {
        $this->updateAttributeUsingData('text_swatch_attribute', $postData);
        $this->assertUpdateAttributeProcess('text_swatch_attribute', $postData, $expectedData);
    }

    /**
     * Test update attribute with error.
     *
     * @dataProvider \Magento\TestFramework\Swatches\Model\Attribute\DataProvider\TextSwatch::getUpdateProviderWithErrorMessage
     * @magentoDataFixture Magento/Swatches/_files/product_text_swatch_attribute.php
     *
     * @param array $postData
     * @param string $errorMessage
     * @return void
     */
    public function testUpdateAttributeWithError(array $postData, string $errorMessage): void
    {
        $this->updateAttributeUsingData('text_swatch_attribute', $postData);
        $this->assertErrorSessionMessages($errorMessage);
    }

    /**
     * Test update attribute frontend labels on stores.
     *
     * @dataProvider \Magento\TestFramework\Swatches\Model\Attribute\DataProvider\TextSwatch::getUpdateFrontendLabelsProvider
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/Swatches/_files/product_text_swatch_attribute.php
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    public function testUpdateFrontendLabelOnStores(array $postData, array $expectedData): void
    {
        $this->processUpdateFrontendLabelOnStores('text_swatch_attribute', $postData, $expectedData);
    }

    /**
     * Test update attribute options on stores.
     *
     * @dataProvider \Magento\TestFramework\Swatches\Model\Attribute\DataProvider\TextSwatch::getUpdateOptionsProvider
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/Swatches/_files/product_text_swatch_attribute.php
     *
     * @param array $postData
     * @return void
     */
    public function testUpdateOptionsOnStores(array $postData): void
    {
        $this->processUpdateOptionsOnStores('text_swatch_attribute', $postData);
    }

    /**
     * @inheritdoc
     */
    protected function getSwatchType(): string
    {
        return Swatch::SWATCH_INPUT_TYPE_TEXT;
    }
}
