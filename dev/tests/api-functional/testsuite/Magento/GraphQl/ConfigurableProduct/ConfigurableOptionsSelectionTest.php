<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\ConfigurableProductGraphQl\Model\Options\SelectionUidFormatter;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Indexer\Model\IndexerFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test configurable product option selection.
 */
class ConfigurableOptionsSelectionTest extends GraphQlAbstract
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var SelectionUidFormatter
     */
    private $selectionUidFormatter;

    /**
     * @var IndexerFactory
     */
    private $indexerFactory;

    /**
     * @var Uid
     */
    private $idEncoder;

    /**
     * @var AttributeInterface
     */
    private $firstConfigurableAttribute;

    /**
     * @var AttributeInterface
     */
    private $secondConfigurableAttribute;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->attributeRepository = Bootstrap::getObjectManager()->create(AttributeRepository::class);
        $this->selectionUidFormatter = Bootstrap::getObjectManager()->create(SelectionUidFormatter::class);
        $this->indexerFactory = Bootstrap::getObjectManager()->create(IndexerFactory::class);
        $this->idEncoder = Bootstrap::getObjectManager()->create(Uid::class);
    }

    /**
     * Test the first option of the first attribute selected
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products_with_two_attributes_combination.php
     */
    public function testSelectedFirstAttributeFirstOption(): void
    {
        $attribute = $this->getFirstConfigurableAttribute();
        $options = $attribute->getOptions();
        $sku = 'configurable_12345';
        $firstOptionUid = $this->selectionUidFormatter->encode(
            (int)$attribute->getAttributeId(),
            (int)$options[1]->getValue()
        );

        $this->reindexAll();
        $response = $this->graphQlQuery($this->getQuery($sku, [$firstOptionUid]));

        self::assertNotEmpty($response['products']['items']);
        $product = current($response['products']['items']);
        self::assertEquals('ConfigurableProduct', $product['__typename']);
        self::assertEquals($sku, $product['sku']);
        self::assertNotEmpty($product['configurable_product_options_selection']['configurable_options']);
        self::assertNull($product['configurable_product_options_selection']['variant']);
        self::assertCount(1, $product['configurable_product_options_selection']['configurable_options']);
        self::assertCount(4, $product['configurable_product_options_selection']['configurable_options'][0]['values']);

        $secondAttributeOptions = $this->getSecondConfigurableAttribute()->getOptions();
        $this->assertAvailableOptionUids(
            $this->getSecondConfigurableAttribute()->getAttributeId(),
            $secondAttributeOptions,
            $this->getOptionsUids(
                $product['configurable_product_options_selection']['configurable_options'][0]['values']
            )
        );

        $this->assertMediaGallery($product);
    }

    /**
     * Test selected variant
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products_with_two_attributes_combination.php
     */
    public function testSelectedVariant(): void
    {
        $firstAttribute = $this->getFirstConfigurableAttribute();
        $firstOptions = $firstAttribute->getOptions();
        $firstAttributeFirstOptionUid = $this->selectionUidFormatter->encode(
            (int)$firstAttribute->getAttributeId(),
            (int)$firstOptions[1]->getValue()
        );
        $secondAttribute = $this->getSecondConfigurableAttribute();
        $secondOptions = $secondAttribute->getOptions();
        $secondAttributeFirstOptionUid = $this->selectionUidFormatter->encode(
            (int)$secondAttribute->getAttributeId(),
            (int)$secondOptions[1]->getValue()
        );

        $sku = 'configurable_12345';

        $this->reindexAll();
        $response = $this->graphQlQuery(
            $this->getQuery($sku, [$firstAttributeFirstOptionUid, $secondAttributeFirstOptionUid])
        );

        self::assertNotEmpty($response['products']['items']);
        $product = current($response['products']['items']);
        self::assertEquals('ConfigurableProduct', $product['__typename']);
        self::assertEquals($sku, $product['sku']);
        self::assertEmpty($product['configurable_product_options_selection']['configurable_options']);
        self::assertNotNull($product['configurable_product_options_selection']['variant']);

        $variantId = $this->idEncoder->decode($product['configurable_product_options_selection']['variant']['uid']);
        self::assertIsNumeric($variantId);
        self::assertIsString($product['configurable_product_options_selection']['variant']['sku']);
        $urlKey = 'configurable-option-first-option-1-second-option-1';
        self::assertEquals($urlKey, $product['configurable_product_options_selection']['variant']['url_key']);

        $this->assertMediaGallery($product);
    }

    /**
     * Test without selected options
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products_with_two_attributes_combination.php
     */
    public function testWithoutSelectedOption(): void
    {
        $sku = 'configurable_12345';
        $this->reindexAll();
        $response = $this->graphQlQuery($this->getQuery($sku));

        self::assertNotEmpty($response['products']['items']);
        $product = current($response['products']['items']);
        self::assertEquals('ConfigurableProduct', $product['__typename']);
        self::assertEquals($sku, $product['sku']);

        self::assertNotEmpty($product['configurable_product_options_selection']['configurable_options']);
        self::assertNull($product['configurable_product_options_selection']['variant']);
        self::assertCount(2, $product['configurable_product_options_selection']['configurable_options']);
        self::assertCount(4, $product['configurable_product_options_selection']['configurable_options'][0]['values']);
        self::assertCount(4, $product['configurable_product_options_selection']['configurable_options'][1]['values']);

        $firstAttributeOptions = $this->getFirstConfigurableAttribute()->getOptions();
        $this->assertAvailableOptionUids(
            $this->getFirstConfigurableAttribute()->getAttributeId(),
            $firstAttributeOptions,
            $this->getOptionsUids(
                $product['configurable_product_options_selection']['configurable_options'][0]['values']
            )
        );

        $secondAttributeOptions = $this->getSecondConfigurableAttribute()->getOptions();
        $this->assertAvailableOptionUids(
            $this->getSecondConfigurableAttribute()->getAttributeId(),
            $secondAttributeOptions,
            $this->getOptionsUids(
                $product['configurable_product_options_selection']['configurable_options'][1]['values']
            )
        );

        $this->assertMediaGallery($product);
    }

    /**
     * Test with wrong selected options
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products_with_two_attributes_combination.php
     */
    public function testWithWrongSelectedOptions(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('configurableOptionValueUids values are incorrect');

        $attribute = $this->getFirstConfigurableAttribute();
        $options = $attribute->getOptions();
        $sku = 'configurable_12345';
        $firstOptionUid = $this->selectionUidFormatter->encode(
            (int)$attribute->getAttributeId(),
            $options[1]->getValue() + 100
        );

        $this->reindexAll();
        $this->graphQlQuery($this->getQuery($sku, [$firstOptionUid]));
    }

    /**
     * Get GraphQL query to test configurable product options selection
     *
     * @param string $productSku
     * @param array $optionValueUids
     * @param int $pageSize
     * @param int $currentPage
     * @return string
     */
    private function getQuery(
        string $productSku,
        array $optionValueUids = [],
        int $pageSize = 20,
        int $currentPage = 1
    ): string {
        if (empty($optionValueUids)) {
            $configurableOptionValueUids = '';
        } else {
            $configurableOptionValueUids = '(configurableOptionValueUids: [';
            foreach ($optionValueUids as $configurableOptionValueUid) {
                $configurableOptionValueUids .= '"' . $configurableOptionValueUid . '",';
            }
            $configurableOptionValueUids .= '])';
        }

        return <<<QUERY
{
products(filter:{
     sku: {eq: "{$productSku}"}
     },
     pageSize: {$pageSize}, currentPage: {$currentPage}
  )
  {
    items {
      __typename
      sku
      ... on ConfigurableProduct {
        configurable_product_options_selection {$configurableOptionValueUids} {
          configurable_options {
            uid
            attribute_code
            label
            values {
              uid
              is_available
              is_use_default
              label
              swatch {
                value
              }
            }
          }
          variant {
            uid
            sku
            url_key
          }
          media_gallery {
            url
            label
            disabled
          }
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Get first configurable attribute.
     *
     * @return AttributeInterface
     * @throws NoSuchEntityException
     */
    private function getFirstConfigurableAttribute(): AttributeInterface
    {
        if (!$this->firstConfigurableAttribute) {
            $this->firstConfigurableAttribute = $this->attributeRepository->get(
                'catalog_product',
                'test_configurable_first'
            );
        }

        return $this->firstConfigurableAttribute;
    }

    /**
     * Get second configurable attribute.
     *
     * @return AttributeInterface
     * @throws NoSuchEntityException
     */
    private function getSecondConfigurableAttribute(): AttributeInterface
    {
        if (!$this->secondConfigurableAttribute) {
            $this->secondConfigurableAttribute = $this->attributeRepository->get(
                'catalog_product',
                'test_configurable_second'
            );
        }

        return $this->secondConfigurableAttribute;
    }

    /**
     * Assert option uid.
     *
     * @param $attributeId
     * @param $expectedOptions
     * @param $selectedOptions
     */
    private function assertAvailableOptionUids($attributeId, $expectedOptions, $selectedOptions): void
    {
        unset($expectedOptions[0]);
        foreach ($expectedOptions as $option) {
            self::assertContains(
                $this->selectionUidFormatter->encode((int)$attributeId, (int)$option->getValue()),
                $selectedOptions
            );
        }
    }

    /**
     * Make fulltext catalog search reindex
     *
     * @return void
     * @throws \Throwable
     */
    private function reindexAll(): void
    {
        $indexLists = [
            'catalog_category_product',
            'catalog_product_category',
            'catalog_product_attribute',
            'cataloginventory_stock',
            'catalogsearch_fulltext',
        ];

        foreach ($indexLists as $indexerId) {
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerId)->reindexAll();
        }
    }

    /**
     * Retrieve options UIDs
     *
     * @param array $options
     * @return array
     */
    private function getOptionsUids(array $options): array
    {
        $uids = [];
        foreach ($options as $option) {
            $uids[] = $option['uid'];
        }
        return $uids;
    }

    /**
     * Assert media gallery fields
     *
     * @param array $product
     */
    private function assertMediaGallery(array $product): void
    {
        self::assertNotEmpty($product['configurable_product_options_selection']['media_gallery']);
        $image = current($product['configurable_product_options_selection']['media_gallery']);
        self::assertIsString($image['url']);
        self::assertEquals(false, $image['disabled']);
    }
}
