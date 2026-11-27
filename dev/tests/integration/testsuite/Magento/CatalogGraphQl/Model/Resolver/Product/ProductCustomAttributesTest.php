<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Catalog\Test\Fixture\MultiselectAttribute as MultiselectAttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\SelectAttribute as SelectAttributeFixture;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests of the attributeCodes argument of ProductInterface.custom_attributesV2
 *
 * The product with a value ("product_with_values") has a value for the text and the select attributes only. The
 * product without value ("product_without_values") has no value for any of the custom attributes.
 */
#[
    AppArea('graphql'),
    DbIsolation(false),
    DataFixture(
        AttributeFixture::class,
        [
            'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
            'attribute_code' => 'it_custom_text',
        ],
        'text_attribute'
    ),
    DataFixture(
        SelectAttributeFixture::class,
        [
            'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
            'attribute_code' => 'it_custom_select',
        ],
        'select_attribute'
    ),
    DataFixture(
        MultiselectAttributeFixture::class,
        [
            'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
            'source_model' => Table::class,
            'backend_model' => ArrayBackend::class,
            'attribute_code' => 'it_custom_multiselect',
        ],
        'multiselect_attribute'
    ),
    DataFixture(
        ProductFixture::class,
        [
            'sku' => 'it-custom-attributes-with-values',
            'custom_attributes' => [
                ['attribute_code' => 'it_custom_text', 'value' => 'text value'],
                ['attribute_code' => 'it_custom_select', 'value' => '$select_attribute.option_2$'],
            ],
        ],
        'product_with_values'
    ),
    DataFixture(
        ProductFixture::class,
        ['sku' => 'it-custom-attributes-without-values'],
        'product_without_values'
    ),
]
class ProductCustomAttributesTest extends TestCase
{
    private const SKU_WITH_VALUES = 'it-custom-attributes-with-values';
    private const SKU_WITHOUT_VALUES = 'it-custom-attributes-without-values';

    /**
     * Requested codes are returned in the requested order, once even when requested several times
     *
     * @return void
     */
    public function testRequestedCodesAreReturnedInOrderWithoutDuplicates(): void
    {
        $result = $this->getCustomAttributes(
            self::SKU_WITH_VALUES,
            'attributeCodes: ["it_custom_select", "it_custom_text", "it_custom_select"]'
        );

        $this->assertSame(['it_custom_select', 'it_custom_text'], array_column($result['items'], 'code'));
        $this->assertSame(
            [['label' => 'option_2', 'value' => $this->getSelectOptionId('option_2')]],
            $result['items'][0]['selected_options']
        );
        $this->assertSame('text value', $result['items'][1]['value']);
        $this->assertSame([], $result['errors']);
    }

    /**
     * Requested attributes without value are returned empty, whatever their input type
     *
     * The select attribute covers the empty option of the table source model, which must not be returned as
     * a selected option.
     *
     * @return void
     */
    public function testRequestedAttributesWithoutValueAreReturnedEmpty(): void
    {
        $result = $this->getCustomAttributes(
            self::SKU_WITHOUT_VALUES,
            'attributeCodes: ["it_custom_text", "it_custom_select", "it_custom_multiselect"]'
        );

        $this->assertSame(
            [
                ['code' => 'it_custom_text', 'value' => ''],
                ['code' => 'it_custom_select', 'selected_options' => []],
                ['code' => 'it_custom_multiselect', 'selected_options' => []],
            ],
            $result['items']
        );
        $this->assertSame([], $result['errors']);
    }

    /**
     * Without attributeCodes, the behaviour is unchanged: attributes without value are not returned
     *
     * @return void
     */
    public function testAttributesWithoutValueAreSkippedWhenNoCodeIsRequested(): void
    {
        $codesWithoutArgument = array_column($this->getCustomAttributes(self::SKU_WITH_VALUES)['items'], 'code');
        $codesWithEmptyList = array_column(
            $this->getCustomAttributes(self::SKU_WITH_VALUES, 'attributeCodes: []')['items'],
            'code'
        );

        $this->assertContains('it_custom_text', $codesWithoutArgument);
        $this->assertContains('it_custom_select', $codesWithoutArgument);
        $this->assertNotContains('it_custom_multiselect', $codesWithoutArgument);
        $this->assertSame($codesWithoutArgument, $codesWithEmptyList, 'An empty list must behave like no argument');
    }

    /**
     * Unknown and non eligible codes are reported as errors, in the requested order, the other codes are returned
     *
     * @return void
     */
    public function testUnavailableCodesAreReported(): void
    {
        $result = $this->getCustomAttributes(
            self::SKU_WITH_VALUES,
            'attributeCodes: ["it_unknown_code", "it_custom_text", "sku"]'
        );

        $this->assertSame([['code' => 'it_custom_text', 'value' => 'text value']], $result['items']);
        $this->assertSame(
            [
                [
                    'type' => 'ATTRIBUTE_NOT_FOUND',
                    'message' => 'Attribute code "it_unknown_code" could not be found.',
                ],
                [
                    'type' => 'ATTRIBUTE_NOT_FOUND',
                    'message' => 'Attribute code "sku" could not be found.',
                ],
            ],
            $result['errors']
        );
    }

    /**
     * Each product of a single request gets its own values, nothing leaks from one product to the other
     *
     * @return void
     */
    public function testRequestedCodesAreResolvedForEachProduct(): void
    {
        $items = $this->queryProducts(
            [self::SKU_WITH_VALUES, self::SKU_WITHOUT_VALUES],
            'attributeCodes: ["it_custom_text"]'
        );

        $this->assertSame(
            [['code' => 'it_custom_text', 'value' => 'text value']],
            $items[self::SKU_WITH_VALUES]['items']
        );
        $this->assertSame(
            [['code' => 'it_custom_text', 'value' => '']],
            $items[self::SKU_WITHOUT_VALUES]['items']
        );
    }

    /**
     * Get the custom_attributesV2 output of a product
     *
     * @param string $sku
     * @param string $arguments
     * @return array
     */
    private function getCustomAttributes(string $sku, string $arguments = ''): array
    {
        $items = $this->queryProducts([$sku], $arguments);
        $this->assertArrayHasKey($sku, $items, 'The product is not returned by the products query.');

        return $items[$sku];
    }

    /**
     * Query the custom_attributesV2 of products, keyed by SKU
     *
     * @param string[] $skus
     * @param string $arguments
     * @return array
     */
    private function queryProducts(array $skus, string $arguments): array
    {
        $skuList = implode(', ', array_map(static fn (string $sku): string => '"' . $sku . '"', $skus));
        $fieldArguments = $arguments === '' ? '' : '(' . $arguments . ')';
        $query = <<<QUERY
{
    products(filter: {sku: {in: [{$skuList}]}}) {
        items {
            sku
            custom_attributesV2{$fieldArguments} {
                items {
                    code
                    ... on AttributeValue { value }
                    ... on AttributeSelectedOptions { selected_options { label value } }
                }
                errors { type message }
            }
        }
    }
}
QUERY;

        $objectManager = Bootstrap::getObjectManager();
        $response = $objectManager->create(GraphQlRequest::class)->send($query);
        $result = $objectManager->get(SerializerInterface::class)->unserialize($response->getContent());
        $this->assertArrayNotHasKey('errors', $result, $response->getContent());

        return array_column($result['data']['products']['items'], 'custom_attributesV2', 'sku');
    }

    /**
     * Get the ID of an option of the select attribute fixture
     *
     * @param string $label
     * @return string
     */
    private function getSelectOptionId(string $label): string
    {
        return (string) DataFixtureStorageManager::getStorage()
            ->get('select_attribute')
            ->getData($label);
    }
}
