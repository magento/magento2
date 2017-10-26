<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Controller;

use GraphQL\Error\FormattedError;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Webapi\Response;
use Magento\GraphQl\Model\SchemaGeneratorInterface;

/**
 * Front controller for web API GraphQL area.
 */
class GraphQl implements FrontControllerInterface
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var SchemaGeneratorInterface
     */
    private $schemaGenerator;

    /**
     * Initialize dependencies
     *
     * @param Response $response
     * @param SchemaGeneratorInterface $schemaGenerator
     */
    public function __construct(
        Response $response,
        SchemaGeneratorInterface $schemaGenerator
    ) {
        $this->response = $response;
        $this->schemaGenerator = $schemaGenerator;
    }

    /**
     * Handle GraphQL request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        try {
            if ($request->getHeader('Content-Type')
                && strpos($request->getHeader('Content-Type'), 'application/json') !== false
            ) {
                $raw = file_get_contents('php://input') ?: '';
                $data = json_decode($raw, true);
            } else {
                $data = $_REQUEST;
            }
            $schema = $this->schemaGenerator->generate();
            $result = \GraphQL\GraphQL::execute(
                $schema,
                isset($data['query']) ? $data['query'] : '',
                null,
                null,
                isset($data['variables']) ? $data['variables'] : []
            );
        } catch (\Exception $error) {
            $result['extensions']['exception'] = FormattedError::createFromException($error);
            $this->response->setStatusCode(500);
        }
        $this->response->setBody(json_encode($result))->setHeader('Content-Type', 'application/json');
        return $this->response;
    }
}
