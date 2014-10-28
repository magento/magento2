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
