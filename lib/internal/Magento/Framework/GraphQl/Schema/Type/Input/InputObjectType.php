<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Input;

use Magento\Framework\GraphQl\Config\Data\WrappedTypeProcessor;
use Magento\Framework\GraphQl\Config\Element\Input as InputConfigElement;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ScalarTypes;
use Magento\Framework\GraphQl\Schema\Type\TypeRegistry;

/**
 * Class InputObjectType
 */
class InputObjectType extends \Magento\Framework\GraphQl\Schema\Type\InputObjectType
{
    /**
     * @var ScalarTypes
     */
    private $scalarTypes;

    /**
     * @var WrappedTypeProcessor
     */
    private $wrappedTypeProcessor;

    /**
     * @var TypeRegistry
     */
    private $typeRegistry;

    /**
     * @param InputConfigElement $configElement
     * @param ScalarTypes $scalarTypes
     * @param WrappedTypeProcessor $wrappedTypeProcessor
     * @param TypeRegistry $typeRegistry
     * @throws GraphQlInputException
     */
    public function __construct(
        InputConfigElement $configElement,
        ScalarTypes $scalarTypes,
        WrappedTypeProcessor $wrappedTypeProcessor,
        TypeRegistry $typeRegistry
    ) {
        $this->scalarTypes = $scalarTypes;
        $this->wrappedTypeProcessor = $wrappedTypeProcessor;
        $this->typeRegistry = $typeRegistry;

        $config = [
            'name' => $configElement->getName(),
            'description' => $configElement->getDescription()
        ];
        foreach ($configElement->getFields() as $field) {
            if ($this->scalarTypes->isScalarType($field->getTypeName())) {
                $type = $type = $this->wrappedTypeProcessor->processScalarWrappedType($field);
            } else {
                if ($field->getTypeName() == $configElement->getName()) {
                    $type = $this;
                } else {
                    $type = $this->typeRegistry->get($field->getTypeName());
                }
                $type = $this->wrappedTypeProcessor->processWrappedType($field, $type);
            }

            $config['fields'][$field->getName()] = [
                'name' => $field->getName(),
                'type' => $type,
                'description'=> $field->getDescription()
            ];
        }
        parent::__construct($config);
    }
}
