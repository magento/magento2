<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\ConfigurableProductGraphQl\Model\Options\SelectionUidFormatter;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test configurable product option selection.
 */
class ConfigurableOptionsSelectionMetadataTest extends GraphQlAbstract
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var SelectionUidFormatter
     */
    private $selectionUidFormatter;

    private $firstConfigurableAttribute = null;

    private $secondConfigurableAttribute = null;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->attributeRepository = Bootstrap::getObjectManager()->create(AttributeRepository::class);
        $this->selectionUidFormatter = Bootstrap::getObjectManager()->create(SelectionUidFormatter::class);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products_with_two_attributes_combination.php
     */
    public function testWithoutSelectedOption()
    {
        $query = <<<QUERY
{
   products(filter:{
     sku: {eq: "configurable_12345"}
         })
   {
    items
     {
      id
      sku
      name
      description {
        html
      }
      ... on ConfigurableProduct {
        configurable_options_selection_metadata(
          selectedConfigurableOptionValues: []
        ) {
          options_available_for_selection {
            option_value_uids
            attribute_code
          }
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertEquals(1, count($response['products']['items']));
        $this->assertEquals(2, count($response['products']['items'][0]['configurable_options_selection_metadata']
            ['options_available_for_selection']));
        $this->assertEquals(4, count($response['products']['items'][0]['configurable_options_selection_metadata']
            ['options_available_for_selection'][0]['option_value_uids']));
        $this->assertEquals(4, count($response['products']['items'][0]['configurable_options_selection_metadata']
            ['options_available_for_selection'][1]['option_value_uids']));
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products_with_two_attributes_combination.php
     */
    public function testSelectedFirstAttributeFirstOption()
    {
        $attribute = $this->getFirstConfigurableAttribute();
        $options = $attribute->getOptions();
        $firstOptionUid = $this->selectionUidFormatter->encode(
            (int)$attribute->getAttributeId(),
            (int)$options[1]->getValue()
        );
        $query = <<<QUERY
{
   products(filter:{
     sku: {eq: "configurable_12345"}
         })
   {
    items
     {
      id
      sku
      name
      description {
        html
      }
      ... on ConfigurableProduct {
        configurable_options_selection_metadata(
          selectedConfigurableOptionValues: ["{$firstOptionUid}"]
        ) {
          options_available_for_selection {
            option_value_uids
            attribute_code
          }
        }
      }
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertEquals(1, count($response['products']['items']));
        $this->assertEquals(2, count($response['products']['items'][0]['configurable_options_selection_metadata']
            ['options_available_for_selection']));
        $this->assertEquals(1, count($response['products']['items'][0]['configurable_options_selection_metadata']
            ['options_available_for_selection'][0]['option_value_uids']));
        $this->assertEquals($firstOptionUid, $response['products']['items'][0]
            ['configurable_options_selection_metadata']['options_available_for_selection'][0]['option_value_uids'][0]);
        $this->assertEquals(4, count($response['products']['items'][0]['configurable_options_selection_metadata']
            ['options_available_for_selection'][1]['option_value_uids']));

        $secondAttributeOptions = $this->getSecondConfigurableAttribute()->getOptions();
        $this->assertAvailableOptionUids(
            $this->getSecondConfigurableAttribute()->getAttributeId(),
            $secondAttributeOptions,
            $response['products']['items'][0]['configurable_options_selection_metadata']
                ['options_available_for_selection'][1]['option_value_uids']
        );
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products_with_two_attributes_combination.php
     */
    public function testSelectedFirstAttributeLastOption()
    {
        $attribute = $this->getFirstConfigurableAttribute();
        $options = $attribute->getOptions();
        $lastOptionUid = $this->selectionUidFormatter->encode(
            (int)$attribute->getAttributeId(),
            (int)$options[4]->getValue()
        );
        $query = <<<QUERY
{
   products(filter:{
     sku: {eq: "configurable_12345"}
         })
   {
    items
     {
      id
      sku
      name
      description {
        html
      }
      ... on ConfigurableProduct {
        configurable_options_selection_metadata(
          selectedConfigurableOptionValues: ["{$lastOptionUid}"]
        ) {
          options_available_for_selection {
            option_value_uids
            attribute_code
          }
        }
      }
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertEquals(1, count($response['products']['items']));
        $this->assertEquals(2, count($response['products']['items'][0]['configurable_options_selection_metadata']
        ['options_available_for_selection']));
        $this->assertEquals(1, count($response['products']['items'][0]['configurable_options_selection_metadata']
        ['options_available_for_selection'][0]['option_value_uids']));
        $this->assertEquals($lastOptionUid, $response['products']['items'][0]['configurable_options_selection_metadata']
        ['options_available_for_selection'][0]['option_value_uids'][0]);
        $this->assertEquals(2, count($response['products']['items'][0]['configurable_options_selection_metadata']
        ['options_available_for_selection'][1]['option_value_uids']));
        $secondAttributeOptions = $this->getSecondConfigurableAttribute()->getOptions();
        unset($secondAttributeOptions[0]);
        unset($secondAttributeOptions[1]);
        unset($secondAttributeOptions[2]);
        $this->assertAvailableOptionUids(
            $this->getSecondConfigurableAttribute()->getAttributeId(),
            $secondAttributeOptions,
            $response['products']['items'][0]['configurable_options_selection_metadata']
            ['options_available_for_selection'][1]['option_value_uids']
        );
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products_with_two_attributes_combination.php
     */
    public function testSelectedVariant()
    {
        $firstAttribute = $this->getFirstConfigurableAttribute();
        $firstOptions = $firstAttribute->getOptions();
        $firstAttributeFirstOptionUid = $this->selectionUidFormatter->encode(
            (int)$firstAttribute->getAttributeId(),
            (int)$firstOptions[1]->getValue()
        );
        $secodnAttribute = $this->getSecondConfigurableAttribute();
        $secondOptions = $secodnAttribute->getOptions();
        $secondAttributeFirstOptionUid = $this->selectionUidFormatter->encode(
            (int)$secodnAttribute->getAttributeId(),
            (int)$secondOptions[1]->getValue()
        );
        $query = <<<QUERY
{
   products(filter:{
     sku: {eq: "configurable_12345"}
         })
   {
    items
     {
      id
      sku
      name
      description {
        html
      }
      ... on ConfigurableProduct {
        configurable_options_selection_metadata(
          selectedConfigurableOptionValues: ["{$firstAttributeFirstOptionUid}", "{$secondAttributeFirstOptionUid}"]
        ) {
          options_available_for_selection {
            option_value_uids
          }
          variant {
            id
            sku
            name
          }
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertEquals(1, count($response['products']['items']));
        $this->assertNotNull($response['products']['items'][0]['configurable_options_selection_metadata']
            ['variant']);
        $this->assertEquals(
            'simple_' . $firstOptions[1]->getValue() . '_' . $secondOptions[1]->getValue(),
            $response['products']['items'][0]['configurable_options_selection_metadata']
            ['variant']['sku']
        );
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products_with_two_attributes_combination.php
     */
    public function testMediaGalleryForAll()
    {
        $query = <<<QUERY
{
   products(filter:{
     sku: {eq: "configurable_12345"}
         })
   {
    items
     {
      id
      sku
      name
      description {
        html
      }
      ... on ConfigurableProduct {
        configurable_options_selection_metadata(
          selectedConfigurableOptionValues: []
        ) {
          options_available_for_selection {
            option_value_uids
            attribute_code
          }
          media_gallery {
            url
          }
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertEquals(1, count($response['products']['items']));
        $this->assertEquals(14, count($response['products']['items'][0]['configurable_options_selection_metadata']
            ['media_gallery']));
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products_with_two_attributes_combination.php
     */
    public function testMediaGalleryWithSelection()
    {
        $attribute = $this->getFirstConfigurableAttribute();
        $options = $attribute->getOptions();
        $lastOptionUid = $this->selectionUidFormatter->encode(
            (int)$attribute->getAttributeId(),
            (int)$options[4]->getValue()
        );
        $query = <<<QUERY
{
   products(filter:{
     sku: {eq: "configurable_12345"}
         })
   {
    items
     {
      id
      sku
      name
      description {
        html
      }
      ... on ConfigurableProduct {
        configurable_options_selection_metadata(
          selectedConfigurableOptionValues: ["$lastOptionUid"]
        ) {
          options_available_for_selection {
            option_value_uids
            attribute_code
          }
          media_gallery {
            url
          }
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertEquals(1, count($response['products']['items']));
        $this->assertEquals(2, count($response['products']['items'][0]['configurable_options_selection_metadata']
            ['media_gallery']));
    }

    /**
     * Assert option uid.
     *
     * @param $attributeId
     * @param $expectedOptions
     * @param $selectedOptions
     */
    private function assertAvailableOptionUids($attributeId, $expectedOptions, $selectedOptions)
    {
        unset($expectedOptions[0]);
        foreach ($expectedOptions as $option) {
            $this->assertContains(
                $this->selectionUidFormatter->encode((int)$attributeId, (int)$option->getValue()),
                $selectedOptions
            );
        }
    }

    /**
     * Get first configurable attribute.
     *
     * @return AttributeInterface
     * @throws NoSuchEntityException
     */
    private function getFirstConfigurableAttribute()
    {
        if (!$this->firstConfigurableAttribute) {
            $attributeCode = 'test_configurable_first';
            $this->firstConfigurableAttribute = $this->attributeRepository->get('catalog_product', $attributeCode);
        }

        return $this->firstConfigurableAttribute;
    }

    /**
     * Get second configurable attribute.
     *
     * @return AttributeInterface
     * @throws NoSuchEntityException
     */
    private function getSecondConfigurableAttribute()
    {
        if (!$this->secondConfigurableAttribute) {
            $attributeCode = 'test_configurable_second';
            $this->secondConfigurableAttribute = $this->attributeRepository->get('catalog_product', $attributeCode);
        }

        return $this->secondConfigurableAttribute;
    }
}
