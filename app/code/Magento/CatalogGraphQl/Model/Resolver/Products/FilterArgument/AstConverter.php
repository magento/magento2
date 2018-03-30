<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\FilterArgument;

use Magento\Framework\GraphQl\Query\Resolver\Argument\AstConverterInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Filter\Clause\ReferenceType;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Filter\Clause\ReferenceTypeFactory;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Filter\ClauseFactory;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Filter\Connective;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Filter\ConnectiveFactory;
use Magento\Framework\GraphQl\Config\Element\InterfaceType;
use Magento\Framework\GraphQl\Config\Element\Type;
use Magento\Framework\GraphQl\ConfigInterface;

/**
 * Converts the input value for "find" to a @see Connective format
 */
class AstConverter implements AstConverterInterface
{
    /**
     * @var ClauseFactory
     */
    private $clauseFactory;

    /**
     * @var ConnectiveFactory
     */
    private $connectiveFactory;

    /**
     * @var ReferenceTypeFactory
     */
    private $referenceTypeFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var array
     */
    private $additionalAttributes;

    /**
     * @param ClauseFactory $clauseFactory
     * @param ConnectiveFactory $connectiveFactory
     * @param ReferenceTypeFactory $referenceTypeFactory
     * @param ConfigInterface $config
     * @param array $additionalAttributes
     */
    public function __construct(
        ClauseFactory $clauseFactory,
        ConnectiveFactory $connectiveFactory,
        ReferenceTypeFactory $referenceTypeFactory,
        ConfigInterface $config,
        array $additionalAttributes = ['min_price', 'max_price']
    ) {
        $this->clauseFactory = $clauseFactory;
        $this->connectiveFactory = $connectiveFactory;
        $this->referenceTypeFactory = $referenceTypeFactory;
        $this->config = $config;
        $this->additionalAttributes = $additionalAttributes;
    }

    /**
     * Get a clause from an AST
     *
     * @param ReferenceType $referenceType
     * @param array $arguments
     * @return array
     */
    private function getClausesFromAst(ReferenceType $referenceType, array $arguments) : array
    {
        $entityInfo = ['attributes' => $this->getCatalogProductFields()];
        $attributes = array_keys($entityInfo['attributes']);
        $conditions = [];
        foreach ($arguments as $argumentName => $argument) {
            if (in_array($argumentName, $attributes)) {
                foreach ($argument as $clauseType => $clause) {
                    if (is_array($clause)) {
                        $value = [];
                        foreach ($clause as $item) {
                            $value[] = $item;
                        }
                    } else {
                        $value = $clause;
                    }
                    $conditions[] = $this->clauseFactory->create(
                        $referenceType,
                        $argumentName,
                        $clauseType,
                        $value
                    );
                }
            } else {
                $conditions[] =
                    $this->connectiveFactory->create(
                        $this->getClausesFromAst($referenceType, $argument),
                        $argumentName
                    );
            }
        }
        return $conditions;
    }

    /**
     * Get the fields from catalog product
     *
     * @return array
     * @throws \LogicException
     */
    private function getCatalogProductFields() : array
    {
        $productTypeSchema = $this->config->getConfigElement('SimpleProduct');
        if (!$productTypeSchema instanceof Type) {
            throw new \LogicException(__("SimpleProduct type not defined in schema."));
        }

        $fields = [];
        foreach ($productTypeSchema->getInterfaces() as $interface) {
            /** @var InterfaceType $configElement */
            $configElement = $this->config->getConfigElement($interface['interface']);

            foreach ($configElement->getFields() as $field) {
                $fields[$field->getName()] = 'String';
            }
        }

        foreach ($this->additionalAttributes as $attribute) {
            $fields[$attribute] = 'String';
        }

        return $fields;
    }

    /**
     * Get a connective filter from an AST input
     *
     * @param string $entityType
     * @param array $arguments
     * @return Connective
     */
    public function convert(string $entityType, array $arguments) : Connective
    {
        $filters =  $this->getClausesFromAst(
            $this->referenceTypeFactory->create($entityType),
            $arguments
        );
        return $this->connectiveFactory->create($filters);
    }
}
