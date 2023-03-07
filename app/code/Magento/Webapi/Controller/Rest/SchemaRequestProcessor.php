<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Model\Rest\Swagger\Generator;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\Framework\Webapi\Request;

/**
 * REST request processor for synchronous "schema" requests
 */
class SchemaRequestProcessor implements RequestProcessorInterface
{

    const PROCESSOR_PATH = 'schema';

    /**
     * Initial dependencies
     *
     * @param Generator $swaggerGenerator
     * @param RestResponse $response
     */
    public function __construct(
        private readonly Generator $swaggerGenerator,
        private readonly RestResponse $response
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function process(RestRequest $request)
    {
        $requestedServices = $request->getRequestedServices('all');
        $requestedServices = $requestedServices == Request::ALL_SERVICES
            ? $this->swaggerGenerator->getListOfServices()
            : $requestedServices;
        $responseBody = $this->swaggerGenerator->generate(
            $requestedServices,
            $request->getScheme(),
            $request->getHttpHost(false),
            $request->getRequestUri()
        );
        $this->response->setBody($responseBody)->setHeader('Content-Type', 'application/json');
    }

    /**
     * {@inheritdoc}
     */
    public function canProcess(RestRequest $request)
    {
        if (strpos(ltrim($request->getPathInfo(), '/'), self::PROCESSOR_PATH) === 0) {
            return true;
        }
        return false;
    }
}
