<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQlCatalog\Model\Resolver\Products\FindArgument;

use Magento\GraphQl\Model\EntityAttributeList;
use Magento\Framework\GraphQl\Argument\Find\Clause\ReferenceTypeFactory;
use Magento\Framework\GraphQl\Argument\Find\Clause\ReferenceType;
use Magento\Framework\GraphQl\Argument\Find\ClauseFactory;
use Magento\Framework\GraphQl\Argument\Find\ConnectiveFactory;
use Magento\Framework\GraphQl\Argument\Find\Connective;
use Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator;

/**
 * Converts the input value for "find" to a @see Connective format
 */
class AstConverter
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
     * @var \Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator
     */
    private $typeGenerator;

    /**
     * @var EntityAttributeList
     */
    private $entityAttributeList;

    /**
     * @param ClauseFactory $clauseFactory
     * @param ConnectiveFactory $connectiveFactory
     * @param ReferenceTypeFactory $referenceTypeFactory
     * @param \Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator $typeGenerator
     * @param EntityAttributeList $entityAttributeList
     */
    public function __construct(
        ClauseFactory $clauseFactory,
        ConnectiveFactory $connectiveFactory,
        ReferenceTypeFactory $referenceTypeFactory,
        TypeGenerator $typeGenerator,
        EntityAttributeList $entityAttributeList
    ) {
        $this->clauseFactory = $clauseFactory;
        $this->connectiveFactory = $connectiveFactory;
        $this->referenceTypeFactory = $referenceTypeFactory;
        $this->typeGenerator = $typeGenerator;
        $this->entityAttributeList = $entityAttributeList;
    }

    /**
     * Get a clause from an AST
     *
     * @param ReferenceType $referenceType
     * @param array $arguments
     * @return array
     */
    private function getClausesFromAst(ReferenceType $referenceType, array $arguments)
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
     */
    private function getCatalogProductFields()
    {
        $result = [];
        $attributes = $this->entityAttributeList->getDefaultEntityAttributes(\Magento\Catalog\Model\Product::ENTITY);
        foreach ($attributes as $attribute) {
            if ((!$attribute->getIsUserDefined()) && !is_array($attribute)) {
                $result[$attribute->getAttributeCode()] = 'String';
            }
        }

        $staticAttributes = $this->typeGenerator->getTypeData('CatalogDataProductInterface');
        foreach ($staticAttributes as $attributeKey => $attribute) {
            if (is_array($attribute)) {
                unset($staticAttributes[$attributeKey]);
            } else {
                $staticAttributes[$attributeKey] = 'String';
            }
        }
        return $result;
    }

    /**
     * Get a connective filter from an AST input
     *
     * @param string $entityType
     * @param array $arguments
     * @return Connective
     */
    public function getFilterFromAst(string $entityType, $arguments)
    {
        $filters =  $this->getClausesFromAst(
            $this->referenceTypeFactory->create($entityType),
            $arguments
        );
        return $this->connectiveFactory->create($filters);
    }
}
