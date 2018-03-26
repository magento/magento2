<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Controller\Rest;

use Magento\Framework\Exception\BulkException;
use Magento\Webapi\Controller\Rest\RequestProcessorInterface;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\WebapiAsync\Controller\Rest\Async\InputParamsResolver;
use Magento\WebapiAsync\Model\MessageQueue\MassSchedule;
use Magento\WebapiAsync\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\WebapiAsync\Api\Data\AsyncResponseInterfaceFactory;

class AsynchronousRequestProcessor implements RequestProcessorInterface
{
    const PROCESSOR_PATH = "/^\\/async(\\/V.+)/";

    /**
     * @var \Magento\Framework\Webapi\Rest\Response
     */
    private $response;

    /**
     * @var \Magento\WebapiAsync\Controller\Rest\Async\InputParamsResolver
     */
    private $inputParamsResolver;

    /**
     * @var \Magento\WebapiAsync\Model\MessageQueue\MassSchedule
     */
    private $asyncBulkPublisher;

    /**
     * @var \Magento\WebapiAsync\Model\ConfigInterface
     */
    private $webapiAsyncConfig;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var \Magento\WebapiAsync\Api\Data\AsyncResponseInterfaceFactory
     */
    private $asyncResponseFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Webapi\Rest\Response $response
     * @param \Magento\WebapiAsync\Controller\Rest\Async\InputParamsResolver $inputParamsResolver
     * @param \Magento\WebapiAsync\Model\MessageQueue\MassSchedule $asyncBulkPublisher
     * @param \Magento\WebapiAsync\Model\ConfigInterface $webapiAsyncConfig
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\WebapiAsync\Api\Data\AsyncResponseInterfaceFactory $asyncResponse
     */
    public function __construct(
        RestResponse $response,
        InputParamsResolver $inputParamsResolver,
        MassSchedule $asyncBulkPublisher,
        WebApiAsyncConfig $webapiAsyncConfig,
        DataObjectProcessor $dataObjectProcessor,
        AsyncResponseInterfaceFactory $asyncResponse
    ) {
        $this->response = $response;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->asyncBulkPublisher = $asyncBulkPublisher;
        $this->webapiAsyncConfig = $webapiAsyncConfig;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->asyncResponseFactory = $asyncResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function process(\Magento\Framework\Webapi\Rest\Request $request)
    {
        $path = $request->getPathInfo();
        $path = preg_replace(self::PROCESSOR_PATH, "$1", $path);
        $request->setPathInfo(
            $path
        );

        $entitiesParamsArray = $this->inputParamsResolver->resolve();
        $topicName = $this->getTopicName($request);
        $requestItemsList = null;

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
            \Magento\WebapiAsync\Api\Data\AsyncResponseInterface::class
        );

        $this->response->setStatusCode(RestResponse::STATUS_CODE_202)
            ->prepareResponse($responseData);
    }

    /**
     * @param \Magento\Framework\Webapi\Rest\Request $request
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
    public function canProcess(\Magento\Framework\Webapi\Rest\Request $request)
    {
        if (preg_match(self::PROCESSOR_PATH, $request->getPathInfo()) === 1) {
            return true;
        }
        return false;
    }
}
