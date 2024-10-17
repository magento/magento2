<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Controller\Rest;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Model\Rest\Swagger\Generator;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\Framework\Webapi\Request;
use Magento\Webapi\Controller\Rest\RequestProcessorInterface;

/**
 * Get schema from request to generate swagger body.
 */
class AsynchronousSchemaRequestProcessor implements RequestProcessorInterface
{
    /**
     * Path for accessing Async Rest API schema
     */
    const PROCESSOR_PATH = 'async/schema';
    const BULK_PROCESSOR_PATH = 'async/bulk/schema';

    /**
     * Initial dependencies
     *
     * @param Generator $swaggerGenerator
     * @param RestResponse $response
     * @param string $processorPath
     */
    public function __construct(
        private readonly Generator $swaggerGenerator,
        private readonly RestResponse $response,
        private $processorPath = self::PROCESSOR_PATH
    ) {
    }

    /**
     * @inheritdoc
     *
     * @return void
     *
     * @throws AuthorizationException
     * @throws InputException
     * @throws Exception
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
            $request->getHttpHost(),
            $request->getRequestUri()
        );
        $this->response->setBody($responseBody)->setHeader('Content-Type', 'application/json');
    }

    /**
     * @inheritdoc
     *
     * @param RestRequest $request
     * @return bool
     */
    public function canProcess(RestRequest $request)
    {
        if (strpos(ltrim($request->getPathInfo(), '/'), (string) $this->processorPath) === 0) {
            return true;
        }
        return false;
    }

    /**
     * Check if a request is a bulk request.
     *
     * @param RestRequest $request
     * @return bool
     */
    public function isBulk(RestRequest $request)
    {
        if (strpos(ltrim($request->getPathInfo(), '/'), self::BULK_PROCESSOR_PATH) === 0) {
            return true;
        }
        return false;
    }
}
