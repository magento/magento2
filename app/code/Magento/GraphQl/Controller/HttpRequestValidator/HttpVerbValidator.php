<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller\HttpRequestValidator;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\Visitor;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\QueryParser;
use Magento\Framework\Phrase;
use Magento\GraphQl\Controller\HttpRequestValidatorInterface;

/**
 * Validator to check HTTP verb for Graphql requests
 */
class HttpVerbValidator implements HttpRequestValidatorInterface
{
    /**
     * @var QueryParser
     */
    private $queryParser;

    /**
     * @param QueryParser|null $queryParser
     */
    public function __construct(QueryParser $queryParser = null)
    {
        $this->queryParser = $queryParser ?: ObjectManager::getInstance()->get(QueryParser::class);
    }

    /**
     * Check if request is using correct verb for query or mutation
     *
     * @param HttpRequestInterface $request
     * @return void
     * @throws GraphQlInputException
     */
    public function validate(HttpRequestInterface $request): void
    {
        /** @var Http $request */
        if (false === $request->isPost()) {
            $query = $request->getParam('query', '');
            if (!empty($query)) {
                $operationType = '';
                $parsedQuery = $this->queryParser->parse($query);
                Visitor::visit(
                    $parsedQuery,
                    [
                        'leave' => [
                            NodeKind::OPERATION_DEFINITION => function (Node $node) use (&$operationType) {
                                $operationType = $node->operation;
                            }
                        ]
                    ]
                );

                if ($operationType !== null && strtolower($operationType) === 'mutation') {
                    throw new GraphQlInputException(
                        new Phrase('Mutation requests allowed only for POST requests')
                    );
                }
            }
        }
    }
}
