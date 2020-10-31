<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Eav\Api\Data\AttributeOptionInterface;

class ProductAttributeOptionsTest extends GraphQlAbstract
{
    /**
     * Test that custom attribute options are returned correctly
     *
     * @magentoApiDataFixture Magento/Catalog/_files/dropdown_attribute.php
     */
    public function testCustomAttributeMetadataOptions()
    {
        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
        $attribute = $eavConfig->getAttribute('catalog_product', 'dropdown_attribute');
        /** @var AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        array_shift($options);
        $optionValues = [];
        // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall
        for ($i = 0; $i < count($options); $i++) {
            $optionValues[] = $options[$i]->getValue();
        }
        $query
            = <<<QUERY
{
  customAttributeMetadata(attributes:
  [
    {
      attribute_code:"description",
      entity_type:"catalog_product"
    },
    {
      attribute_code:"status",
      entity_type:"catalog_product"
    },
    {
      attribute_code:"dropdown_attribute",
      entity_type:"catalog_product"
    }
  ]
  )
  {
    items
    {
      attribute_code
      attribute_type
      entity_type
      input_type
      attribute_options{
        label
        value
      }
    } 
  }
 }
QUERY;
        $response = $this->graphQlQuery($query);

        $expectedOptionArray = [
            [], // description attribute has no options
            [
                [
                    'label' => 'Enabled',
                    'value' => '1'
                ],
                [
                    'label' => 'Disabled',
                    'value' => '2'
                ]
            ],
            [
                [
                    'label' => 'Option 1',
                    'value' => $optionValues[0]
                ],
                [
                    'label' => 'Option 2',
                    'value' => $optionValues[1]
                ],
                [
                    'label' => 'Option 3',
                    'value' => $optionValues[2]
                ]
            ]
        ];

        $this->assertNotEmpty($response['customAttributeMetadata']['items']);
        $actualAttributes = $response['customAttributeMetadata']['items'];

        foreach ($expectedOptionArray as $index => $expectedOptions) {
            $actualOption = $actualAttributes[$index]['attribute_options'];
            $this->assertEquals($expectedOptions, $actualOption);
        }
    }
}
