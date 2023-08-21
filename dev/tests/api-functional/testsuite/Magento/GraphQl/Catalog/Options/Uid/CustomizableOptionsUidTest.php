<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog\Options\Uid;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for product custom options uid
 */
class CustomizableOptionsUidTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_full_option_set.php
     */
    public function testQueryUidForCustomizableOptions()
    {
        $productSku = 'simple';
        $query = $this->getQuery($productSku);
        $response = $this->graphQlQuery($query);
        $responseProduct = $response['products']['items'][0];
        self::assertNotEmpty($responseProduct['options']);

        foreach ($responseProduct['options'] as $option) {
            if (isset($option['entered_option'])) {
                $enteredOption = $option['entered_option'];
                $uid = $this->getUidForEnteredValue($option['option_id']);

                self::assertEquals($uid, $enteredOption['uid']);
            } elseif (isset($option['selected_option'])) {
                $this->assertNotEmpty($option['selected_option']);

                foreach ($option['selected_option'] as $selectedOption) {
                    $uid = $this->getUidForSelectedValue($option['option_id'], $selectedOption['option_type_id']);
                    self::assertEquals($uid, $selectedOption['uid']);
                }
            }
        }
    }

    /**
     * Get uid for entered option
     *
     * @param int $optionId
     *
     * @return string
     */
    private function getUidForEnteredValue(int $optionId): string
    {
        return base64_encode('custom-option/' . $optionId);
    }

    /**
     * Get uid for selected option
     *
     * @param int $optionId
     * @param int $optionValueId
     *
     * @return string
     */
    private function getUidForSelectedValue(int $optionId, int $optionValueId): string
    {
        return base64_encode('custom-option/' . $optionId . '/' . $optionValueId);
    }

    /**
     * Get query
     *
     * @param string $sku
     *
     * @return string
     */
    private function getQuery(string $sku): string
    {
        return <<<QUERY
query {
  products(filter: { sku: { eq: "$sku" } }) {
    items {
      sku

      ... on CustomizableProductInterface {
        options {
          option_id
          title

          ... on CustomizableRadioOption {
            option_id
            selected_option: value {
              option_type_id
              uid
            }
          }

          ... on CustomizableDropDownOption {
            option_id
            selected_option: value {
              option_type_id
              uid
            }
          }

          ... on CustomizableMultipleOption {
            option_id
            selected_option: value {
              option_type_id
              uid
            }
          }

          ... on CustomizableCheckboxOption {
            option_id
            selected_option: value {
              option_type_id
              uid
            }
          }

          ... on CustomizableAreaOption {
            option_id
            entered_option: value {
              uid
            }
          }

          ... on CustomizableFieldOption {
            option_id
            entered_option: value {
              uid
            }
          }

          ... on CustomizableFileOption {
            option_id
            entered_option: value {
              uid
            }
          }

          ... on CustomizableDateOption {
            option_id
            entered_option: value {
              uid
            }
          }
        }
      }
    }
  }
}
QUERY;
    }
}
