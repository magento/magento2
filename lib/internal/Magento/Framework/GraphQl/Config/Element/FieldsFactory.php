<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

/**
 * Fields object factory
 */
class FieldsFactory
{
    /**
     * @var ArgumentFactory
     */
    private $argumentFactory;

    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @param ArgumentFactory $argumentFactory
     * @param FieldFactory $fieldFactory
     */
    public function __construct(
        ArgumentFactory $argumentFactory,
        FieldFactory $fieldFactory
    ) {
        $this->argumentFactory = $argumentFactory;
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * Create a fields object from a configured array with optional arguments.
     *
     * Field data must contain name and type. Other values are optional and include required, itemType, description,
     * and resolver. Arguments array must be in the format of [$argumentData['name'] => $argumentData].
     *
     * @param array $fieldsData
     * @return Field[]
     */
    public function createFromConfigData(
        array $fieldsData
    ) : array {
        $fields = [];
        foreach ($fieldsData as $fieldData) {
            $arguments = [];
            foreach ($fieldData['arguments'] as $argumentData) {
                $arguments[$argumentData['name']] = $this->argumentFactory->createFromConfigData($argumentData);
            }
            $fields[$fieldData['name']] = $this->fieldFactory->createFromConfigData(
                $fieldData,
                $arguments
            );
        }
        return $fields;
    }
}
