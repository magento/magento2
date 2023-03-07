<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Framework\Webapi\Rest\Response\FieldsFilter;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;

/**
 * REST request processor for synchronous requests
 */
class SynchronousRequestProcessor implements RequestProcessorInterface
{
    const PROCESSOR_PATH = "/^\\/V\\d+/";

    /**
     * Initial dependencies
     *
     * @param \Magento\Framework\Webapi\Rest\Response $response
     * @param \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver
     * @param \Magento\Framework\Webapi\ServiceOutputProcessor $serviceOutputProcessor
     * @param \Magento\Framework\Webapi\Rest\Response\FieldsFilter $fieldsFilter
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        private readonly RestResponse $response,
        private readonly InputParamsResolver $inputParamsResolver,
        private readonly ServiceOutputProcessor $serviceOutputProcessor,
        private readonly FieldsFilter $fieldsFilter,
        private readonly DeploymentConfig $deploymentConfig,
        private readonly ObjectManagerInterface $objectManager
    ) {
    }

    /**
     *  {@inheritdoc}
     */
    public function process(Request $request)
    {
        $inputParams = $this->inputParamsResolver->resolve();

        $route = $this->inputParamsResolver->getRoute();
        $serviceMethodName = $route->getServiceMethod();
        $serviceClassName = $route->getServiceClass();
        $service = $this->objectManager->get($serviceClassName);

        /**
         * @var AbstractExtensibleObject $outputData
         */
        $outputData = call_user_func_array([$service, $serviceMethodName], $inputParams);
        $outputData = $this->serviceOutputProcessor->process(
            $outputData,
            $serviceClassName,
            $serviceMethodName
        );
        if ($request->getParam(FieldsFilter::FILTER_PARAMETER) && is_array($outputData)) {
            $outputData = $this->fieldsFilter->filter($outputData);
        }
        $header = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_X_FRAME_OPT);
        if ($header) {
            $this->response->setHeader('X-Frame-Options', $header);
        }
        $this->response->prepareResponse($outputData);
    }

    /**
     * {@inheritdoc}
     */
    public function canProcess(Request $request)
    {
        if (preg_match(self::PROCESSOR_PATH, $request->getPathInfo()) === 1) {
            return true;
        }
        return false;
    }
}
