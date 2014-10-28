<?php
/**
 * Soap API request.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $allowedParams = array($wsdlParam, $servicesParam);
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
        $serviceArray = array();
        foreach ($serviceVersionArray as $service) {
            $serviceArray[] = $service;
        }
        return $serviceArray;
    }
}
