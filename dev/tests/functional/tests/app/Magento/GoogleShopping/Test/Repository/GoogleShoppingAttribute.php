<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleShopping\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class GoogleShoppingAttribute
 * Data for creation Google Shopping Attribute
 */
class GoogleShoppingAttribute extends AbstractRepository
{
    /**
     * Construct
     *
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'target_country' => 'United States',
            'attribute_set_id' => ['dataSet' => 'default'],
            'category' => 'Apparel & Accessories',
        ];
    }
}
