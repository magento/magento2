<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

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
     * or throw \Exception if at least one request entity params is not valid
     *
     * @return array
     * @throws \Magento\Framework\Exception\InputException if no value is provided for required parameters
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function resolve()
    {
        $this->requestValidator->validate();
        $webapiResolvedParams = [];

        ///single item request
        $webapiResolvedParams[] = $this->inputParamsResolver->resolve();

        return $webapiResolvedParams;
    }

    /**
     * @return \Magento\Webapi\Controller\Rest\Router\Route
     */
    public function getRoute()
    {
        return $this->inputParamsResolver->getRoute();
    }
}
