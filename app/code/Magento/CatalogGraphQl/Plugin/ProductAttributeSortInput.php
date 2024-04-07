<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Plugin;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\Visitor;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

class ProductAttributeSortInput
{
    /**
     * Plugin to preserve the original order of sort fields
     *
     * @param \Magento\Framework\GraphQl\Query\ResolverInterface $subject
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeResolve(
        \Magento\Framework\GraphQl\Query\ResolverInterface $subject,
        \Magento\Framework\GraphQl\Config\Element\Field $field,
        ContextInterface $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        if (isset($args['sort'])) {
            $args['sort'] = $this->getSortFieldsOrder($info, $args['sort']);
        }
        return [$field, $context, $info, $value, $args];
    }

    /**
     * Get sort fields in the original order
     *
     * @param ResolveInfo $info
     * @param array $sortFields
     * @return array
     * @throws \Exception
     */
    private function getSortFieldsOrder(ResolveInfo $info, array $sortFields)
    {
        $sortFieldsOriginal = [];
        Visitor::visit(
            $info->operation,
            [
                'enter' => [
                    NodeKind::ARGUMENT => function (Node $node) use (&$sortFieldsOriginal, $sortFields) {
                        if ($node->name->value === 'sort') {
                            Visitor::visit(
                                $node->value,
                                [
                                    'enter' => [
                                        NodeKind::OBJECT_FIELD =>
                                            function (Node $node) use (&$sortFieldsOriginal, $sortFields) {
                                                if (isset($sortFields[$node->name->value])) {
                                                    $sortFieldsOriginal[$node->name->value] =
                                                        $sortFields[$node->name->value];
                                                }
                                            }
                                    ]
                                ]
                            );
                            return Visitor::stop();
                        }
                    }
                ]
            ]
        );
        return $sortFieldsOriginal;
    }
}
