<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Framework\Webapi\Rest\Response\FieldsFilter;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;

/**
 * REST request processor for general synchronous requests
 */
class SynchronousRequestProcessor implements RequestProcessorInterface
{
    const SYNC_PATH = "/V1/";

    /**
     * @var RestResponse
     */
    protected $_response;

    /**
     * @var InputParamsResolver
     */
    protected $inputParamsResolver;

    /**
     * @var ServiceOutputProcessor
     */
    protected $serviceOutputProcessor;

    /**
     * @var FieldsFilter
     */
    protected $fieldsFilter;

    protected $deploymentConfig;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        RestResponse $response,
        InputParamsResolver $inputParamsResolver,
        ServiceOutputProcessor $serviceOutputProcessor,
        FieldsFilter $fieldsFilter,
        DeploymentConfig $deploymentConfig,
        ObjectManagerInterface $objectManager
    )
    {
        $this->_response = $response;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->fieldsFilter = $fieldsFilter;
        $this->deploymentConfig = $deploymentConfig;
        $this->_objectManager = $objectManager;
    }



    /**
     *  {@inheritdoc}
     */
    public function process(\Magento\Framework\Webapi\Rest\Request $request)
    {
        $inputParams = $this->inputParamsResolver->resolve();

        $route = $this->inputParamsResolver->getRoute();
        $serviceMethodName = $route->getServiceMethod();
        $serviceClassName = $route->getServiceClass();

        $service = $this->_objectManager->get($serviceClassName);
        /** @var \Magento\Framework\Api\AbstractExtensibleObject $outputData */
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
            $this->_response->setHeader('X-Frame-Options', $header);
        }
        $this->_response->prepareResponse($outputData);
    }



}