<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Data\Mapper;

use Magento\Framework\GraphQl\Config\Data\StructureInterface;
use \Magento\Framework\GraphQl\Config\Data\DataFactory;

/**
 * {@inheritdoc}
 */
class EnumMapper implements StructureMapperInterface
{
    /**
     * @var DataFactory
     */
    private $dataFactory;

    /**
     * @param DataFactory $dataFactory
     */
    public function __construct(DataFactory $dataFactory)
    {
        $this->dataFactory = $dataFactory;
    }

    /**
     * {@inheritdoc}
     *
     * Map a configured enum type to an Enum structure object.
     *
     * @param array $data
     * @return StructureInterface
     */
    public function map(array $data): StructureInterface
    {
        $values = [];
        foreach ($data['items'] as $item) {
            $values[$item['_value']] = $this->dataFactory->createValue($item['name'], $item['_value']);
        }
        return $this->dataFactory->createEnum(
            $data['name'],
            $values
        );
    }
}
