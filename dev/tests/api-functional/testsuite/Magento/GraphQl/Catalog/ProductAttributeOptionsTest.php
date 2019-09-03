<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductAttributeOptionsTest extends GraphQlAbstract
{
    /**
     * Test that custom attribute options are returned correctly
     *
     * @magentoApiDataFixture Magento/Catalog/_files/dropdown_attribute.php
     */
    public function testCustomAttributeMetadataOptions()
    {
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
                    'value' => '10'
                ],
                [
                    'label' => 'Option 2',
                    'value' => '11'
                ],
                [
                    'label' => 'Option 3',
                    'value' => '12'
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
