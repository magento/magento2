<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class Product Repository
 *
 */
class AssignProducts extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'config' => $defaultConfig,
            'data' => $defaultData,
        ];
        $this->_data['add_' . $defaultConfig['assignType '] . '_products'] = $this->_data['default'];
        $this->_data['add_' . $defaultConfig['assignType '] . '_product'] = $this->withOneProduct(
            $defaultConfig['assignType ']
        );
    }

    /**
     * @param string $type
     * @return array
     */
    protected function withOneProduct($type)
    {
        $data = $this->_data['default'];
        unset($data['data']['fields'][$type . '_products']['value']['product_2']);
        return $data;
    }
}
