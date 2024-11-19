<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Controller\Rest;

use Magento\Framework\Exception\BulkException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Webapi\Controller\Rest\RequestProcessorInterface;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\WebapiAsync\Controller\Rest\Asynchronous\InputParamsResolver;
use Magento\AsynchronousOperations\Model\MassSchedule;
use Magento\AsynchronousOperations\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\AsynchronousOperations\Api\Data\AsyncResponseInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\AsyncResponseInterface;

/**
 * Responsible for dispatching single and bulk requests.
 * Single requests dispatching represented by this class.
 * Bulk requests dispatching represented by virtualType of this class.
 */
class AsynchronousRequestProcessor implements RequestProcessorInterface
{
    const PROCESSOR_PATH = "/^\\/async(\\/V.+)/";
    const BULK_PROCESSOR_PATH = "/^\\/async\/bulk(\\/V.+)/";

    /**
     * @var AsyncResponseInterfaceFactory
     */
    private $asyncResponseFactory;

    /**
     * Initialize dependencies.
     *
     * @param RestResponse $response
     * @param InputParamsResolver $inputParamsResolver
     * @param MassSchedule $asyncBulkPublisher
     * @param WebApiAsyncConfig $webapiAsyncConfig
     * @param DataObjectProcessor $dataObjectProcessor
     * @param AsyncResponseInterfaceFactory $asyncResponse
     * @param string $processorPath
     */
    public function __construct(
        private readonly RestResponse $response,
        private readonly InputParamsResolver $inputParamsResolver,
        private readonly MassSchedule $asyncBulkPublisher,
        private readonly WebApiAsyncConfig $webapiAsyncConfig,
        private readonly DataObjectProcessor $dataObjectProcessor,
        AsyncResponseInterfaceFactory $asyncResponse,
        private $processorPath = self::PROCESSOR_PATH
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request)
    {
        $path = $request->getPathInfo();
        $path = preg_replace($this->processorPath, "$1", $path);
        $request->setPathInfo(
            $path
        );

        $entitiesParamsArray = $this->inputParamsResolver->resolve();
        $topicName = $this->getTopicName($request);

        try {
            $asyncResponse = $this->asyncBulkPublisher->publishMass(
                $topicName,
                $entitiesParamsArray
            );
        } catch (BulkException $bulkException) {
            $asyncResponse = $bulkException->getData();
        }

        $responseData = $this->dataObjectProcessor->buildOutputDataArray(
            $asyncResponse,
            AsyncResponseInterface::class
        );

        $this->response->setStatusCode(RestResponse::STATUS_CODE_202)
            ->prepareResponse($responseData);
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getTopicName($request)
    {
        $route = $this->inputParamsResolver->getRoute();

        return $this->webapiAsyncConfig->getTopicName(
            $route->getRoutePath(),
            $request->getHttpMethod()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function canProcess(Request $request)
    {
        if ($request->getHttpMethod() === Request::HTTP_METHOD_GET) {
            return false;
        }

        if (preg_match($this->processorPath, $request->getPathInfo()) === 1) {
            return true;
        }
        return false;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isBulk(Request $request)
    {
        if (preg_match(self::BULK_PROCESSOR_PATH, $request->getPathInfo()) === 1) {
            return true;
        }
        return false;
    }
}
