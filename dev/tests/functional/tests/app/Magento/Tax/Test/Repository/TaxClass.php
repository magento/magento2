<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class TaxClass Repository
 */
class TaxClass extends AbstractRepository
{
    /**
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['Taxable Goods'] = [
            'class_id' => '2',
            'class_name' => 'Taxable Goods',
            'class_type' => 'PRODUCT',
            'id' => '2',
            'mtf_dataset_name' => 'Taxable Goods',
        ];

        $this->_data['Retail Customer'] = [
            'class_id' => '3',
            'class_name' => 'Retail Customer',
            'class_type' => 'CUSTOMER',
            'id' => '3',
            'mtf_dataset_name' => 'Retail Customer',
        ];

        $this->_data['customer_tax_class'] = [
            'class_name' => 'Customer Tax Class %isolation%',
            'class_type' => 'CUSTOMER',
        ];

        $this->_data['product_tax_class'] = [
            'class_name' => 'Product Tax Class %isolation%',
            'class_type' => 'PRODUCT',
        ];

        $this->_data['None'] = [
            'class_name' => 'None',
            'class_type' => 'PRODUCT',
            'id' => '0',
        ];

        $this->_data['all'] = [
            'class_name' => 'All',
        ];
    }
}
