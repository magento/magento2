<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Fixture;

use Magento\Catalog\Test\Fixture\SelectAttribute;
use Magento\Framework\DataObject;

class Attribute extends SelectAttribute
{
    private const DEFAULT_DATA = [
        'scope' => 'global',
        'backend_type' => 'int'
    ];

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = $this->prepareData($data);

        return parent::apply($data);
    }

    /**
     * Prepare attribute data
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
