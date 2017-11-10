<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\Model\Resolver\Products;

use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\SelectionNode;
use GraphQL\Language\AST\NodeList;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\GraphQl\Model\GraphQl\Clause\ReferenceTypeFactory;
use Magento\GraphQl\Model\GraphQl\Clause\ReferenceType;
use Magento\GraphQl\Model\GraphQl\ClauseFactory;
use Magento\GraphQl\Model\GraphQl\ConnectiveFactory;
use Magento\GraphQl\Model\GraphQl\Connective;
use Magento\GraphQl\Model\Type\Helper\ServiceContract\TypeGenerator;

/**
 * Product field resolver, used for GraphQL request processing
 */
class ProductFilterResolver
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
     * @var TypeGenerator
     */
    private $typeGenerator;

    /**
     * @var AttributeManagementInterface
     */
    private $management;

    /**
     * @param ClauseFactory $clauseFactory
     * @param ConnectiveFactory $connectiveFactory
     * @param ReferenceTypeFactory $referenceTypeFactory
     * @param TypeGenerator $typeGenerator
     * @param AttributeManagementInterface $management
     */
    public function __construct(
        ClauseFactory $clauseFactory,
        ConnectiveFactory $connectiveFactory,
        ReferenceTypeFactory $referenceTypeFactory,
        TypeGenerator $typeGenerator,
        AttributeManagementInterface $management
    ) {
        $this->clauseFactory = $clauseFactory;
        $this->connectiveFactory = $connectiveFactory;
        $this->referenceTypeFactory = $referenceTypeFactory;
        $this->typeGenerator = $typeGenerator;
        $this->management = $management;
    }

    /**
     * Get a clause from an AST
     *
     * @param ReferenceType $referenceType
     * @param NodeList $nodeList
     * @return array
     */
    private function getClausesFromAst(ReferenceType $referenceType, NodeList $nodeList)
    {
        $entityInfo = ['attributes' => $this->getCatalogProductFields()];
        $attributes = array_keys($entityInfo['attributes']);
        $conditions = [];
        foreach ($nodeList as $field) {
            if (in_array($field->name->value, $attributes)) {
                foreach ($field->value->fields as $clauseInfo) {
                    if ($clauseInfo->value instanceof ListValueNode) {
                        $value = [];
                        foreach ($clauseInfo->value->values as $item) {
                            $value[] = $item->value;
                        }
                    } else {
                        $value = $clauseInfo->value->value;
                    }
                    $conditions[] = $this->clauseFactory->create(
                        $referenceType,
                        $field->name->value,
                        $clauseInfo->name->value,
                        $value
                    );
                }
            } else {
                $conditions[] =
                    $this->connectiveFactory->create(
                        $this->getClausesFromAst($referenceType, $field->value->fields),
                        $field->name->value
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
        $attributes = $this->management->getAttributes('catalog_product', 4);
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
     * Get a connective filter from an AST
     *
     * @param string $entityType
     * @param SelectionNode $node
     * @return Connective
     */
    public function getFilterFromAst(string $entityType, SelectionNode $node)
    {
        $filters = [];
        foreach ($node->arguments as $argument) {
            if ($argument->name->value == 'find') {
                $filters =  $this->getClausesFromAst(
                    $this->referenceTypeFactory->create($entityType),
                    $argument->value->fields
                );
            }
        }
        return $this->connectiveFactory->create($filters);
    }
}
