<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for config elements of 'field' type.
 */
class FieldFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create a field object from a configured array with optional arguments.
     *
     * Field data must contain name and type. Other values are optional and include required, itemType, description,
     * and resolver. Arguments array must be in the format of [$argumentData['name'] => $argumentData].
     *
     * @param array $fieldData
     * @param array $arguments
     * @return Field
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function createFromConfigData(
        array $fieldData,
        array $arguments = []
    ) : Field {
        $fieldType = $fieldData['type'];
        $isList = false;

        //check if type ends with []
        if ($fieldType[strlen((string)$fieldType) - 2] == '[' && $fieldType[strlen((string)$fieldType) - 1] == ']') {
            $isList = true;
            $fieldData['type'] = str_replace('[]', '', $fieldData['type'] ?? '');
            $fieldData['itemType'] = str_replace('[]', '', $fieldData['type'] ?? '');
        }

        return $this->objectManager->create(
            Field::class,
            [
                'name' => $fieldData['name'],
                'type' => $fieldData['type'],
                'required' => isset($fieldData['required']) ? $fieldData['required'] : false,
                'isList' => isset($fieldData['itemType']) || $isList,
                'itemType' => isset($fieldData['itemType']) ? $fieldData['itemType'] : '',
                'resolver' => isset($fieldData['resolver']) ? $fieldData['resolver'] : '',
                'description' => isset($fieldData['description']) ? $fieldData['description'] : '',
                'arguments' => $arguments,
                'cache' => isset($fieldData['cache']) ? $fieldData['cache'] : [],
                'deprecated' => isset($fieldData['deprecated']) ? $fieldData['deprecated'] : [],
            ]
        );
    }
}
