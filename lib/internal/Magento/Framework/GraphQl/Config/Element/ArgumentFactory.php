<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

use Magento\Framework\ObjectManagerInterface;

/**
 * @inheritdoc
 */
class ArgumentFactory
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
     * Create an argument object based off a configured Output/InputInterface's data.
     *
     * Argument data must contain name and type. Other values are optional and include baseType, itemType, description,
     * required, and itemsRequired.
     *
     * @param array $argumentData
     * @return Argument
     */
    public function createFromConfigData(
        array $argumentData
    ) : Argument {
        return $this->objectManager->create(
            Argument::class,
            [
                'name' => $argumentData['name'],
                'type' => isset($argumentData['itemType']) ? $argumentData['itemType'] : $argumentData['type'],
                'baseType' => isset($argumentData['baseType']) ? $argumentData['baseType'] : '',
                'description' => isset($argumentData['description']) ? $argumentData['description'] : '',
                'required' => isset($argumentData['required']) ? $argumentData['required'] : false,
                'isList' => isset($argumentData['itemType']),
                'itemType' => isset($argumentData['itemType']) ? $argumentData['itemType'] : '',
                'itemsRequired' => isset($argumentData['itemsRequired']) ? $argumentData['itemsRequired'] : false,
                'defaultValue' => isset($argumentData['defaultValue']) ? $argumentData['defaultValue'] : null,
                'deprecated' => isset($argumentData['deprecated']) ? $argumentData['deprecated'] : [],
            ]
        );
    }
}
