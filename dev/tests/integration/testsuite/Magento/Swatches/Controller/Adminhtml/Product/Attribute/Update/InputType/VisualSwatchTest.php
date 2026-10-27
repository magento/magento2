<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Swatches\Controller\Adminhtml\Product\Attribute\Update\InputType;

use Magento\Swatches\Controller\Adminhtml\Product\Attribute\Update\AbstractUpdateSwatchAttributeTest;
use Magento\Swatches\Model\Swatch;
use PHPUnit\Framework\Attributes\DataProviderExternal;

/**
 * Test cases related to update attribute with input type visual swatch.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class VisualSwatchTest extends AbstractUpdateSwatchAttributeTest
{
    /**
     * Test update attribute.
     *
     * @magentoDataFixture Magento/Swatches/_files/product_visual_swatch_attribute.php
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\Swatches\Model\Attribute\DataProvider\VisualSwatch::class, 'getUpdateProvider')]
    public function testUpdateAttribute(array $postData, array $expectedData): void
    {
        $this->updateAttributeUsingData('visual_swatch_attribute', $postData);
        $this->assertUpdateAttributeProcess('visual_swatch_attribute', $postData, $expectedData);
    }

    /**
     * Test update attribute with error.
     *
     * @magentoDataFixture Magento/Swatches/_files/product_visual_swatch_attribute.php
     *
     * @param array $postData
     * @param string $errorMessage
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\Swatches\Model\Attribute\DataProvider\VisualSwatch::class, 'getUpdateProviderWithErrorMessage')]
    public function testUpdateAttributeWithError(array $postData, string $errorMessage): void
    {
        $this->updateAttributeUsingData('visual_swatch_attribute', $postData);
        $this->assertErrorSessionMessages($errorMessage);
    }

    /**
     * Test update attribute frontend labels on stores.
     *
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/Swatches/_files/product_visual_swatch_attribute.php
     *
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\Swatches\Model\Attribute\DataProvider\VisualSwatch::class, 'getUpdateFrontendLabelsProvider')]
    public function testUpdateFrontendLabelOnStores(array $postData, array $expectedData): void
    {
        $this->processUpdateFrontendLabelOnStores('visual_swatch_attribute', $postData, $expectedData);
    }

    /**
     * Test update attribute options on stores.
     *
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture Magento/Swatches/_files/product_visual_swatch_attribute.php
     *
     * @param array $postData
     * @return void
     */
    #[DataProviderExternal(\Magento\TestFramework\Swatches\Model\Attribute\DataProvider\VisualSwatch::class, 'getUpdateOptionsProvider')]
    public function testUpdateOptionsOnStores(array $postData): void
    {
        $this->processUpdateOptionsOnStores('visual_swatch_attribute', $postData);
    }

    /**
     * @inheritdoc
     */
    protected function getSwatchType(): string
    {
        return Swatch::SWATCH_INPUT_TYPE_VISUAL;
    }
}
