<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Controller\Rest\Async;

use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Webapi\Controller\Rest\ParamsOverrider;
use Magento\Webapi\Controller\Rest\RequestValidator;
use Magento\Webapi\Controller\Rest\InputParamsResolver as WebapiInputParamsResolver;

/**
 * This class is responsible for retrieving resolved input data
 */
class InputParamsResolver
{
    /**
     * @var RestRequest
     */
    private $request;

    /**
     * @var ParamsOverrider
     */
    private $paramsOverrider;

    /**
     * @var ServiceInputProcessor
     */
    private $serviceInputProcessor;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var RequestValidator
     */
    private $requestValidator;

    /**
     * @var \Magento\Webapi\Controller\Rest\InputParamsResolver
     */
    private $inputParamsResolver;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param \Magento\Webapi\Controller\Rest\ParamsOverrider $paramsOverrider
     * @param \Magento\Framework\Webapi\ServiceInputProcessor $inputProcessor
     * @param \Magento\Webapi\Controller\Rest\Router $router
     * @param \Magento\Webapi\Controller\Rest\RequestValidator $requestValidator
     * @param \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver
     */
    public function __construct(
        RestRequest $request,
        ParamsOverrider $paramsOverrider,
        ServiceInputProcessor $inputProcessor,
        Router $router,
        RequestValidator $requestValidator,
        WebapiInputParamsResolver $inputParamsResolver
    ) {
        $this->request = $request;
        $this->paramsOverrider = $paramsOverrider;
        $this->serviceInputProcessor = $inputProcessor;
        $this->router = $router;
        $this->requestValidator = $requestValidator;
        $this->inputParamsResolver = $inputParamsResolver;
    }

    /**
     * Process and resolve input parameters
     * Return array with validated input params
     * or \Exception object for failed validation params
     *
     * @return array
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function resolve()
    {
        $this->requestValidator->validate();
        $webapiResolvedParams = [];
        $inputData = $this->request->getRequestData();

        //simple check if async request have single or bulk entities
        if (array_key_exists(0, $inputData)) {
            foreach ($inputData as $key => $singleParams) {
                try {
                    $webapiResolvedParams[$key] = $this->resolveParams($singleParams);
                } catch (\Exception $e) {
                    //return input request data when failed to process rejected type in MassSchedule
                    $webapiResolvedParams[$key] = $singleParams;
                }
            }
        } else {//single item request
            try {
                $webapiResolvedParams[] = $this->resolveParams($inputData);
            } catch (\Exception $e) {
                //return input request data when failed to process rejected type in MassSchedule
                $webapiResolvedParams[] = $inputData;
            }
        }

        return $webapiResolvedParams;
    }

    /**
     * @return \Magento\Webapi\Controller\Rest\Router\Route
     */
    public function getRoute()
    {
        return $this->inputParamsResolver->getRoute();
    }

    /**
     * @return array|\Exception
     */
    private function resolveParams($inputData)
    {
        $route = $this->getRoute();
        $serviceMethodName = $route->getServiceMethod();
        $serviceClassName = $route->getServiceClass();

        /*
         * Valid only for updates using PUT when passing id value both in URL and body
         */
        if ($this->request->getHttpMethod() == RestRequest::HTTP_METHOD_PUT) {
            $inputData = $this->paramsOverrider->overrideRequestBodyIdWithPathParam(
                $this->request->getParams(),
                $inputData,
                $serviceClassName,
                $serviceMethodName
            );
            $inputData = array_merge($inputData, $this->request->getParams());
        }

        $inputData = $this->paramsOverrider->override($inputData, $route->getParameters());
        $inputParams = $this->serviceInputProcessor->process($serviceClassName, $serviceMethodName, $inputData);

        return $inputParams;
    }
}
