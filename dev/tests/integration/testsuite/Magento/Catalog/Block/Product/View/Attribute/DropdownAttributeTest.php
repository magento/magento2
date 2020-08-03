<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View\Attribute;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Class checks dropdown attribute displaying on frontend
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class DropdownAttributeTest extends AbstractAttributeTest
{
    /** @var string */
    private $attributeCode;

    /**
     * @return void
     */
    public function testAttributeView(): void
    {
        $attributeValue = $this->getAttribute()->getSource()->getOptionId('Option 2');
        $this->processAttributeView('simple2', $attributeValue, 'Option 2');
    }

    /**
     * @return void
     */
    public function testAttributeWithNonDefaultValueView(): void
    {
        $attributeValue = $this->getAttribute()->getSource()->getOptionId('Option 2');
        $this->processNonDefaultAttributeValueView('simple2', $attributeValue, 'Option 2');
    }

    /**
     * @dataProvider attributeWithTagsProvider
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute_with_html.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @param bool $allowHtmlTags
     * @param string $attributeValue
     * @param string $expectedAttributeValue
     * @return void
     */
    public function testAttributeWithHtmlTags(
        bool $allowHtmlTags,
        string $attributeValue,
        string $expectedAttributeValue
    ): void {
        $this->attributeCode = 'dropdown_attribute_with_html';
        $attributeValue =  $this->getAttribute()->getSource()->getOptionId($attributeValue);
        $this->processAttributeHtmlOutput('simple2', $allowHtmlTags, $attributeValue, $expectedAttributeValue);
    }

    /**
     * @return array
     */
    public function attributeWithTagsProvider(): array
    {
        return [
            'allow_html_tags' => [
                'allow_html_tags' => true,
                'attribute_value' => '<h2>Option 2</h2>',
                'expected_attribute_value' => '<h2>Option 2</h2>',
            ],
            'disallow_html_tags' => [
                'allow_html_tags' => false,
                'attribute_value' => '<h2>Option 2</h2>',
                'expected_attribute_value' => '&lt;h2&gt;Option 2&lt;/h2&gt;',
            ],
        ];
    }

    /**
     * @magentoDbIsolation disabled
     *
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @return void
     */
    public function testAttributePerStoreView(): void
    {
        $this->processMultiStoreView(
            'simple2',
            ScopedAttributeInterface::SCOPE_STORE,
            'Option 3',
            'fixturestore'
        );
    }

    /**
     * @magentoDbIsolation disabled
     *
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_two_websites.php
     *
     * @return void
     */
    public function testAttributePerWebsites(): void
    {
        $this->processMultiStoreView(
            'simple-on-two-websites',
            ScopedAttributeInterface::SCOPE_WEBSITE,
            'Option 3',
            'fixture_second_store'
        );
    }

    /**
     * @inheritdoc
     */
    protected function getAttributeCode(): string
    {
        return $this->attributeCode ?? 'dropdown_attribute';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultAttributeValue(): string
    {
        return $this->getAttribute()->getSource()->getOptionId('Option 1');
    }
}
