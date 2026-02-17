<?php
/**
 * Copyright 2025 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Controller\Rest;

use Magento\Framework\Exception\BulkException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;
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
    public const PROCESSOR_PATH = "/^\\/async(\\/V.+)/";
    public const BULK_PROCESSOR_PATH = "/^\\/async\/bulk(\\/V.+)/";

    /**
     * @var \Magento\Framework\Webapi\Rest\Response
     */
    private $response;
    /**
     * @var \Magento\WebapiAsync\Controller\Rest\Asynchronous\InputParamsResolver
     */
    private $inputParamsResolver;
    /**
     * @var MassSchedule
     */
    private $asyncBulkPublisher;
    /**
     * @var WebApiAsyncConfig
     */
    private $webapiAsyncConfig;
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataObjectProcessor;
    /**
     * @var AsyncResponseInterfaceFactory
     */
    private $asyncResponseFactory;
    /**
     * @var string Regex pattern
     */
    private $processorPath;

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
        RestResponse $response,
        InputParamsResolver $inputParamsResolver,
        MassSchedule $asyncBulkPublisher,
        WebApiAsyncConfig $webapiAsyncConfig,
        DataObjectProcessor $dataObjectProcessor,
        AsyncResponseInterfaceFactory $asyncResponse,
        $processorPath = self::PROCESSOR_PATH
    ) {
        $this->response = $response;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->asyncBulkPublisher = $asyncBulkPublisher;
        $this->webapiAsyncConfig = $webapiAsyncConfig;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->asyncResponseFactory = $asyncResponse;
        $this->processorPath = $processorPath;
    }

    /**
     * @inheritdoc
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
     * Get Topic Name
     *
     * @param Request $request
     * @return string
     * @throws InputException
     * @throws LocalizedException
     * @throws Exception
     */
    private function getTopicName(Request $request): string
    {
        $route = $this->inputParamsResolver->getRoute();

        return $this->webapiAsyncConfig->getTopicName(
            $route->getRoutePath(),
            $request->getHttpMethod()
        );
    }

    /**
     * @inheritdoc
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
     * To check if it is bulk
     *
     * @param Request $request
     * @return bool
     */
    public function isBulk(Request $request): bool
    {
        if (preg_match(self::BULK_PROCESSOR_PATH, $request->getPathInfo()) === 1) {
            return true;
        }
        return false;
    }
}
