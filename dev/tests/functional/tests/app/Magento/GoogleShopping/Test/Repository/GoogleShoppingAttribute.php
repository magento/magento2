<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
