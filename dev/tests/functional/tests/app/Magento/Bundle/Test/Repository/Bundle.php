<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Bundle\Test\Repository;

use Magento\Catalog\Test\Fixture;
use Magento\Catalog\Test\Repository\Product;

/**
 * Class Product Repository
 *
 */
class Bundle extends Product
{
    /**
     * @param string $productType
     * @return array
     */
    protected function resetRequiredFields($productType)
    {
        $required = parent::resetRequiredFields($productType);
        if (isset($this->_data[$productType]['data']['fields']['price'])) {
            $required = array_merge_recursive(
                $required,
                [
                    'data' => [
                        'fields' => [
                            'price' => [
                                'value' => 60,
                                'group' => Fixture\Product::GROUP_PRODUCT_DETAILS,
                            ],
                        ],
                        'checkout' => [
                            'prices' => [
                                'price_from' => 70,
                                'price_to' => 72,
                            ],
                        ],
                    ]
                ]
            );
        } else {
            $required['data']['checkout']['prices'] = $this->_data[$productType]['data']['checkout']['prices'];
        }
        return $required;
    }
}
