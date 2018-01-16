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
class TypeInterfaceMapper implements StructureMapperInterface
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
     * Map a configured GraphQL interface to an Interface structure object.
     *
     * @param array $data
     * @return StructureInterface
     */
    public function map(array $data): StructureInterface
    {
        $fields = [];
        foreach ($data['fields'] as $field) {
            $arguments = [];
            foreach ($field['arguments'] as $argument) {
                $arguments[$argument['name']] = $this->dataFactory->createArgument($argument);
            }
            $fields[$field['name']] = $this->dataFactory->createField($field, $arguments);
        }
        return $this->dataFactory->createInterface(
            $data,
            $fields
        );
    }
}
