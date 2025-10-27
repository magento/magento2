<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View\Attribute;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Class checks text attribute displaying on frontend
 *
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Catalog/_files/product_varchar_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class TextAttributeTest extends AbstractAttributeTest
{
    /**
     * @return void
     */
    public function testAttributeView(): void
    {
        $attributeValue = 'Text attribute value';
        $this->processAttributeView('simple2', $attributeValue, $attributeValue);
    }

    /**
     * @return void
     */
    public function testAttributeWithNonDefaultValueView(): void
    {
        $attributeValue = 'Non default text attribute value';
        $this->processNonDefaultAttributeValueView('simple2', $attributeValue, $attributeValue);
    }

    /**
     * @return void
     */
    public function testAttributeWithDefaultValueView(): void
    {
        $this->processDefaultValueAttributeView('simple2', $this->getDefaultAttributeValue());
    }

    /**
     * @dataProvider attributeWithTagsProvider
     * @magentoAppArea frontend
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
        $this->processAttributeHtmlOutput('simple2', $allowHtmlTags, $attributeValue, $expectedAttributeValue);
    }

    /**
     * @return array
     */
    public static function attributeWithTagsProvider(): array
    {
        return [
            'allow_html_tags' => [
                'allowHtmlTags' => true,
                'attributeValue' => '<h2>Text with <p>html inside</p></h2>',
                'expectedAttributeValue' => '<h2>Text with <p>html inside</p></h2>',
            ],
            'disallow_html_tags' => [
                'allowHtmlTags' => false,
                'attributeValue' => '<h2>Text with <p>html inside</p></h2>',
                'expectedAttributeValue' => '&lt;h2&gt;Text with &lt;p&gt;html inside&lt;/p&gt;&lt;/h2&gt;',
            ],
        ];
    }

    /**
     * @magentoDbIsolation disabled
     *
     * @magentoDataFixture Magento/Catalog/_files/product_varchar_attribute.php
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
            'second store view value',
            'fixturestore'
        );
    }

    /**
     * @magentoDbIsolation disabled
     *
     * @magentoDataFixture Magento/Catalog/_files/product_two_websites.php
     * @magentoDataFixture Magento/Catalog/_files/product_varchar_attribute.php
     *
     * @return void
     */
    public function testAttributePerWebsites(): void
    {
        $this->processMultiStoreView(
            'simple-on-two-websites',
            ScopedAttributeInterface::SCOPE_WEBSITE,
            'second website value',
            'fixture_second_store'
        );
    }

    /**
     * @inheritdoc
     */
    protected function getAttributeCode(): string
    {
        return 'varchar_attribute';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultAttributeValue(): string
    {
        return 'Default value for text attribute';
    }
}
