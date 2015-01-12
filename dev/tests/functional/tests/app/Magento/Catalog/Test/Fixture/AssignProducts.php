<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture;

use Mtf\Factory\Factory;
use Mtf\System\Config;

class AssignProducts extends Product
{
    protected $assignType = '';

    protected $group = '';

    /**
     * {@inheritdoc}
     */
    public function __construct(Config $configuration, $placeholders = [])
    {
        parent::__construct($configuration, $placeholders);

        $this->_placeholders[$this->assignType . '_simple::getSku'] = [$this, 'productProvider'];
        $this->_placeholders[$this->assignType . '_simple::getName'] = [$this, 'productProvider'];
        $this->_placeholders[$this->assignType . '_configurable::getSku'] = [$this, 'productProvider'];
        $this->_placeholders[$this->assignType . '_configurable::getName'] = [$this, 'productProvider'];
    }

    /**
     * Init Data
     */
    protected function _initData()
    {
        $this->_dataConfig = [
            'assignType ' => $this->assignType,
        ];
        $this->_data = [
            'fields' => [
                $this->assignType . '_products' => [
                    'value' => [
                        'product_1' => [
                            'sku' => '%' . $this->assignType . '_simple::getSku%',
                            'name' => '%' . $this->assignType . '_simple::getName%',
                        ],
                        'product_2' => [
                            'sku' => '%' . $this->assignType . '_configurable::getSku%',
                            'name' => '%' . $this->assignType . '_configurable::getName%',
                        ],
                    ],
                    'group' => $this->group,
                ],
            ],
        ];

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoCatalogAssignProducts($this->_dataConfig, $this->_data);
    }

    /**
     * @param string $productData
     * @return string
     */
    protected function formatProductType($productData)
    {
        return str_replace($this->assignType . '_', '', $productData);
    }
}
