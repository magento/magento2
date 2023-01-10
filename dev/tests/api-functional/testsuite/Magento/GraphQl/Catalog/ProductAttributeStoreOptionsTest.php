<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Exception;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductAttributeStoreOptionsTest extends GraphQlAbstract
{
    /**
     * Test that custom attribute option labels are returned respecting store
     *
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_attribute_store_options.php
     * @throws LocalizedException
     */
    public function testAttributeStoreLabels(): void
    {
        $this->attributeLabelTest('Option Default Store');
        $this->attributeLabelTest('Option Test Store', ['Store' => 'test']);
    }

    /**
     * @param $expectedLabel
     * @param array $headers
     * @throws LocalizedException
     * @throws Exception
     */
    private function attributeLabelTest($expectedLabel, array $headers = []): void
    {
        /** @var Config $eavConfig */
        $eavConfig = Bootstrap::getObjectManager()->get(Config::class);
        $attributeCode = 'test_configurable';
        $attribute = $eavConfig->getAttribute('catalog_product', $attributeCode);

        /** @var AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        array_shift($options);
        $optionValues = [];

        foreach ($options as $option) {
            $optionValues[] = [
                'value' => $option->getValue(),
            ];
        }

        $expectedOptions = [
            [
                'label' => $expectedLabel,
                'value' => $optionValues[0]['value']
            ]
        ];

        $query = <<<QUERY
{
    products(search:"Simple",
         pageSize: 3
         currentPage: 1
       )
  {
    aggregations
    {
        attribute_code
        options
        {
          label
          value
        }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query, [], '', $headers);
        $this->assertNotEmpty($response['products']['aggregations']);
        $actualAttributes = $response['products']['aggregations'];
        $actualAttributeOptions = [];

        foreach ($actualAttributes as $actualAttribute) {
            if ($actualAttribute['attribute_code'] === $attributeCode) {
                $actualAttributeOptions = $actualAttribute['options'];
            }
        }

        $this->assertNotEmpty($actualAttributeOptions);

        foreach ($actualAttributeOptions as $key => $actualAttributeOption) {
            if ($actualAttributeOption['value'] === $expectedOptions[$key]['value']) {
                $this->assertEquals($actualAttributeOption['label'], $expectedOptions[$key]['label']);
            }
        }
    }
}
