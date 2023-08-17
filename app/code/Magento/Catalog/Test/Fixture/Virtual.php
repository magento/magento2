<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\DataObject;

class Virtual extends Product
{
    private const DEFAULT_DATA = [
        'type_id' => Type::TYPE_VIRTUAL,
        'name' => 'Virtual Product%uniqid%',
        'sku' => 'virtual-product%uniqid%',
        'weight' => null,
    ];

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        return parent::apply($this->prepareData($data));
    }

    /**
     * Prepare product data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);

        return $data;
    }
}
