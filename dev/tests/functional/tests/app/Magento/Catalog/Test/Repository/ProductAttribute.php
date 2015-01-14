<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class Product Attribute Repository
 *
 */
class ProductAttribute extends AbstractRepository
{
    /**
     * Construct
     *
     * @param array $defaultConfig
     * @param array $defaultData
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'config' => $defaultConfig,
            'data' => $defaultData,
        ];

        $this->_data['configurable_attribute'] = $this->_data['default'];

        $this->_data['new_attribute'] = [
            'config' => $defaultConfig,
            'data' => $this->buildNewAttributeData($defaultData),
        ];
    }

    /**
     * Build new attribute data set
     *
     * @param array $defaultData
     * @return array
     */
    protected function buildNewAttributeData(array $defaultData)
    {
        unset($defaultData['fields']['is_configurable']);
        unset($defaultData['fields']['attribute_code']);
        return $defaultData;
    }
}
