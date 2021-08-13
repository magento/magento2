<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller\HttpRequestValidator;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\App\Request\Http;
use Magento\GraphQl\Controller\HttpRequestValidatorInterface;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;

/**
 * Validator to check HTTP verb for Graphql requests
 */
class HttpVerbValidator implements HttpRequestValidatorInterface
{
    /**
     * Check if request is using correct verb for query or mutation
     *
     * @param HttpRequestInterface $request
     * @return void
     * @throws GraphQlInputException
     */
    public function validate(HttpRequestInterface $request) : void
    {
        /** @var Http $request */
        if (false === $request->isPost()) {
            $query = $request->getParam('query', '');
            if (!empty($query)) {
                $operationType = null;
                $queryAst = \GraphQL\Language\Parser::parse(new \GraphQL\Language\Source($query ?: '', 'GraphQL'));
                \GraphQL\Language\Visitor::visit(
                    $queryAst,
                    [
                        'leave' => [
                            NodeKind::OPERATION_DEFINITION => function (Node $node) use (&$operationType) {
                                $operationType = $node->operation;
                            }
                        ]
                    ]
                );

                if (strtolower($operationType) === 'mutation') {
                    throw new GraphQlInputException(
                        new \Magento\Framework\Phrase('Mutation requests allowed only for POST requests')
                    );
                }
            }
        }
    }
}
