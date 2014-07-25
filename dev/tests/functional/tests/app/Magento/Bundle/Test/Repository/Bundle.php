<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Test\Repository;

use Magento\Catalog\Test\Repository\Product;
use Magento\Catalog\Test\Fixture;

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
                                'group' => Fixture\Product::GROUP_PRODUCT_DETAILS
                            ]
                        ],
                        'checkout' => [
                            'prices' => [
                                'price_from' => 70,
                                'price_to' => 72
                            ]
                        ]
                    ]
                ]
            );
        } else {
            $required['data']['checkout']['prices'] = $this->_data[$productType]['data']['checkout']['prices'];
        }
        return $required;
    }
}
