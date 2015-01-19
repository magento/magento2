<?php
/**
 * Soap API request.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller\Soap;

class Request extends \Magento\Webapi\Controller\Request
{
    /**
     * Identify versions of resources that should be used for API configuration generation.
     *
     * @return array
     * @throws \Magento\Webapi\Exception When GET parameters are invalid
     */
    public function getRequestedServices()
    {
        $wsdlParam = \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_WSDL;
        $servicesParam = \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_SERVICES;
        $requestParams = array_keys($this->getParams());
        $allowedParams = [$wsdlParam, $servicesParam];
        $notAllowedParameters = array_diff($requestParams, $allowedParams);
        if (count($notAllowedParameters)) {
            $notAllowed = implode(', ', $notAllowedParameters);
            $message = __(
                'Not allowed parameters: %1. Please use only %2 and %3.',
                $notAllowed,
                $wsdlParam,
                $servicesParam
            );
            throw new \Magento\Webapi\Exception($message);
        }

        $param = $this->getParam($servicesParam);
        return $this->_convertRequestParamToServiceArray($param);
    }

    /**
     * Extract the resources query param value and return associative array of the form 'resource' => 'version'
     *
     * @param string $param eg <pre> testModule1AllSoapAndRestV1,testModule2AllSoapNoRestV1 </pre>
     * @return array <pre> eg array (
     *      'testModule1AllSoapAndRest' => 'V1',
     *       'testModule2AllSoapNoRest' => 'V1',
     *      )</pre>
     * @throws \Magento\Webapi\Exception
     */
    protected function _convertRequestParamToServiceArray($param)
    {
        $serviceSeparator = ',';
        $serviceVerPattern = "[a-zA-Z\d]*V[\d]+";
        $regexp = "/^({$serviceVerPattern})([{$serviceSeparator}]{$serviceVerPattern})*\$/";
        //Check if the $param is of valid format
        if (empty($param) || !preg_match($regexp, $param)) {
            $message = __('Incorrect format of WSDL request URI or Requested services are missing.');
            throw new \Magento\Webapi\Exception($message);
        }
        //Split the $param string to create an array of 'service' => 'version'
        $serviceVersionArray = explode($serviceSeparator, $param);
        $serviceArray = [];
        foreach ($serviceVersionArray as $service) {
            $serviceArray[] = $service;
        }
        return $serviceArray;
    }
}
