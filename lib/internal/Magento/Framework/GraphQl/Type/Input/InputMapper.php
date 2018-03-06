<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Input;

use Magento\Framework\GraphQl\Config\ConfigInterface;
use Magento\Framework\GraphQl\Config\Data\Argument;
use GraphQL\Type\Definition\InputType;
use Magento\Framework\GraphQl\TypeFactory;

/**
 * Class OutputMapper
 */
class InputMapper
{
    /**
     * @var InputFactory
     */
    private $inputFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param InputFactory $inputFactory
     * @param ConfigInterface $config
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        InputFactory $inputFactory,
        ConfigInterface $config,
        TypeFactory $typeFactory
    ) {
        $this->inputFactory = $inputFactory;
        $this->config = $config;
        $this->typeFactory = $typeFactory;
    }

    /**
     * @param Argument $argument
     * @return InputType
     */
    public function getRepresentation(Argument $argument) : InputType
    {
        $type = $argument->isList() ? $argument->getItemType() : $argument->getType();
        $instance = $this->typeFactory->createScalar($type);
        if (!$instance) {
            $configElement = $this->config->getTypeStructure($type);
            $instance = $this->inputFactory->create($configElement);
        }
        if ($argument->isList()) {
            $instance = $argument->areItemsRequired() ? $this->typeFactory->createNonNull($instance) : $instance;
            $instance = $this->typeFactory->createList($instance);
        }
        $instance = $argument->isRequired() ? $this->typeFactory->createNonNull($instance) : $instance;

        return $instance;
    }

    public function getFieldRepresentation(string $type) : InputType
    {
        $instance = $this->typeFactory->createScalar($type);
        if (!$instance) {
            $configElement = $this->config->getTypeStructure($type);
            $instance = $this->inputFactory->create($configElement);
        }
        return $instance;
    }
}
