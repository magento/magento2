<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Plugin;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\Visitor;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;

class ProductAttributeSortInput
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @param RequestInterface $request
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(RequestInterface $request, SerializerInterface $jsonSerializer)
    {
        $this->request = $request;
        $this->jsonSerializer = $jsonSerializer;
    }
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
        ?array $value = null,
        ?array $args = null
    ): array {

        $data = $this->getDataFromRequest($this->request);
        if (isset($args['sort'])) {
            $args['sort'] = $this->getSortFieldsOrder(
                $info,
                $args['sort'],
                isset($data['variables']['sort']) ? array_keys($data['variables']['sort']) : null
            );
        }
        return [$field, $context, $info, $value, $args];
    }

    /**
     * Get data from request body or query string
     *
     * @param RequestInterface $request
     * @return array
     */
    private function getDataFromRequest(RequestInterface $request): array
    {
        $data = [];
        if ($request->isPost()) {
            $data = $this->jsonSerializer->unserialize($request->getContent());
        } elseif ($request->isGet()) {
            $data = $request->getParams();
            $data['variables'] = isset($data['variables']) ?
                $this->jsonSerializer->unserialize($data['variables']) : null;
            $data['variables'] = is_array($data['variables']) ?
                $data['variables'] : null;
        }
        return $data;
    }

    /**
     * Get sort fields in the original order
     *
     * @param ResolveInfo $info
     * @param array $sortFields
     * @param array|null $requestSortFields
     * @return array
     * @throws \Exception
     */
    private function getSortFieldsOrder(ResolveInfo $info, array $sortFields, ?array $requestSortFields): array
    {
        $sortFieldsOriginal = [];
        Visitor::visit(
            $info->operation,
            [
                'enter' => [
                    NodeKind::ARGUMENT => function (Node $node) use (
                        &$sortFieldsOriginal,
                        $sortFields,
                        $requestSortFields
                    ) {
                        if ($node->name->value === 'sort') {
                            if (isset($requestSortFields)) {
                                foreach ($requestSortFields as $fieldName) {
                                    if (isset($sortFields[$fieldName])) {
                                        $sortFieldsOriginal[$fieldName] = $sortFields[$fieldName];
                                    }
                                }
                            } else {
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
                            }
                            return Visitor::stop();
                        }
                    }
                ]
            ]
        );
        return $sortFieldsOriginal;
    }
}
