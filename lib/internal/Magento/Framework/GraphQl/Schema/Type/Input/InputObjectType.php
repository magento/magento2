<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Input;

use Magento\Framework\GraphQl\Config\Data\WrappedTypeProcessor;
use Magento\Framework\GraphQl\Config\Element\Type as TypeConfigElement;
use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\GraphQl\Schema\Type\ScalarTypes;
use Magento\Framework\GraphQl\Schema\TypeFactory;

/**
 * Class InputObjectType
 */
class InputObjectType extends \Magento\Framework\GraphQl\Schema\Type\InputObjectType
{
    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var ScalarTypes
     */
    private $scalarTypes;

    /**
     * @var WrappedTypeProcessor
     */
    private $wrappedTypeProcessor;

    /**
     * @var InputFactory
     */
    private $inputFactory;

    /**
     * @var ConfigInterface
     */
    public $graphQlConfig;

    /**
     * @param TypeConfigElement $configElement
     * @param TypeFactory $typeFactory
     * @param ScalarTypes $scalarTypes
     * @param WrappedTypeProcessor $wrappedTypeProcessor
     * @param InputFactory $inputFactory
     * @param ConfigInterface $graphQlConfig
     */
    public function __construct(
        TypeConfigElement $configElement,
        TypeFactory $typeFactory,
        ScalarTypes $scalarTypes,
        WrappedTypeProcessor $wrappedTypeProcessor,
        InputFactory $inputFactory,
        ConfigInterface $graphQlConfig
    ) {
        $this->typeFactory = $typeFactory;
        $this->scalarTypes = $scalarTypes;
        $this->wrappedTypeProcessor = $wrappedTypeProcessor;
        $this->inputFactory = $inputFactory;
        $this->graphQlConfig = $graphQlConfig;
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
                    $fieldConfigElement = $this->graphQlConfig->getConfigElement($field->getTypeName());
                    $type = $this->inputFactory->create($fieldConfigElement);
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
