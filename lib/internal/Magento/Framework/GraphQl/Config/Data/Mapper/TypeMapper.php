<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Data\Mapper;

use Magento\Framework\GraphQl\Config\Data\StructureInterface;
use \Magento\Framework\GraphQl\Config\Data\DataFactory;

class TypeMapper implements StructureMapperInterface
{
    /**
     * @var DataFactory
     */
    private $dataFactory;

    /**
     * EnumMapper constructor.
     *
     * @param DataFactory $dataFactory
     */
    public function __construct(DataFactory $dataFactory)
    {
        $this->dataFactory = $dataFactory;
    }

    /**
     * {@inheritdoc}
     *
     * Map a configured GraphQL type to a Type structure object.
     *
     * @param array $data
     * @return StructureInterface
     */
    public function map(array $data): StructureInterface
    {
        $fields = [];
        $data['fields'] = isset($data['fields']) ? $data['fields'] : [];
        foreach ($data['fields'] as $field) {
            $arguments = [];
            foreach ($field['arguments'] as $argument) {
                $arguments[$argument['name']] = $this->dataFactory->createArgument($argument);
            }
            $fields[$field['name']] = $this->dataFactory->createField(
                $field,
                $arguments
            );
        }
        return $this->dataFactory->createType(
            $data,
            $fields
        );
    }
}
