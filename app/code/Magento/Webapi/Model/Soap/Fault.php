<?php
/**
 * Magento-specific SOAP fault.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Soap;

use Magento\Framework\App\State;

class Fault
{
    const FAULT_REASON_INTERNAL = 'Internal Error.';

    /**#@+
     * Fault codes that are used in SOAP faults.
     */
    const FAULT_CODE_SENDER = 'Sender';
    const FAULT_CODE_RECEIVER = 'Receiver';

    /**#@+
     * Nodes that can appear in Detail node of SOAP fault.
     */
    const NODE_DETAIL_PARAMETERS = 'Parameters';
    const NODE_DETAIL_WRAPPED_ERRORS = 'WrappedErrors';
    const NODE_DETAIL_WRAPPED_EXCEPTION = 'WrappedException';
    /* Note that parameter node must be unique in scope of all complex types declared in WSDL */
    const NODE_DETAIL_PARAMETER = 'GenericFaultParameter';
    const NODE_DETAIL_PARAMETER_KEY = 'key';
    const NODE_DETAIL_PARAMETER_VALUE = 'value';
    const NODE_DETAIL_WRAPPED_ERROR = 'WrappedError';
    const NODE_DETAIL_WRAPPED_ERROR_MESSAGE = 'message';
    const NODE_DETAIL_WRAPPED_ERROR_PARAMETERS = 'parameters';
    const NODE_DETAIL_WRAPPED_ERROR_PARAMETER = 'parameter';
    const NODE_DETAIL_WRAPPED_ERROR_KEY = 'key';
    const NODE_DETAIL_WRAPPED_ERROR_VALUE = 'value';
    const NODE_DETAIL_TRACE = 'Trace';
    const NODE_DETAIL_WRAPPER = 'GenericFault';
    /**#@-*/

    /** @var string */
    protected $_soapFaultCode;

    /**
     * Parameters are extracted from exception and can be inserted into 'Detail' node as 'Parameters'.
     *
     * @var array
     */
    protected $_parameters = [];

    /**
     * Wrapped errors are extracted from exception and can be inserted into 'Detail' node as 'WrappedErrors'.
     *
     * @var array
     */
    protected $_wrappedErrors = [];

    /**
     * Fault name is used for details wrapper node name generation.
     *
     * @var string
     */
    protected $_faultName = '';

    /**
     * Details that are used to generate 'Detail' node of SoapFault.
     *
     * @var array
     */
    protected $_details = [];

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var Server
     */
    protected $_soapServer;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var null|string
     */
    protected $stackTrace;

    /**
     * @var string
     */
    protected $message;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Server $soapServer
     * @param \Magento\Framework\Webapi\Exception $exception
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param State $appState
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        Server $soapServer,
        \Magento\Framework\Webapi\Exception $exception,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        State $appState
    ) {
        $this->_soapCode = $exception->getOriginator();
        $this->_parameters = $exception->getDetails();
        $this->_wrappedErrors = $exception->getErrors();
        $this->stackTrace = $exception->getStackTrace() ?: $exception->getTraceAsString();
        $this->message = $exception->getMessage();
        $this->_request = $request;
        $this->_soapServer = $soapServer;
        $this->_localeResolver = $localeResolver;
        $this->appState = $appState;
    }

    /**
     * Render exception as XML.
     *
     * @return string
     */
    public function toXml()
    {
        if ($this->appState->getMode() == State::MODE_DEVELOPER) {
            $this->addDetails([self::NODE_DETAIL_TRACE => "<![CDATA[{$this->stackTrace}]]>"]);
        }
        if ($this->getParameters()) {
            $this->addDetails([self::NODE_DETAIL_PARAMETERS => $this->getParameters()]);
        }
        if ($this->getWrappedErrors()) {
            $this->addDetails([self::NODE_DETAIL_WRAPPED_ERRORS => $this->getWrappedErrors()]);
        }

        return $this->getSoapFaultMessage($this->getMessage(), $this->getSoapCode(), $this->getDetails());
    }

    /**
     * Retrieve additional details about current fault.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * Retrieve wrapped errors about current fault.
     *
     * @return array
     */
    public function getWrappedErrors()
    {
        return $this->_wrappedErrors;
    }

    /**
     * Add details about current fault.
     *
     * @param array $details Associative array containing details about current fault
     * @return $this
     */
    public function addDetails($details)
    {
        $this->_details = array_merge($this->_details, $details);
        return $this;
    }

    /**
     * Retrieve additional details about current fault.
     *
     * @return array
     */
    public function getDetails()
    {
        return $this->_details;
    }

    /**
     * Retrieve SOAP fault code.
     *
     * @return string
     */
    public function getSoapCode()
    {
        return $this->_soapCode;
    }

    /**
     * Retrieve SOAP fault language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return \Locale::getPrimaryLanguage($this->_localeResolver->getLocale());
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Generate SOAP fault message in XML format.
     *
     * @param string $reason Human-readable explanation of the fault
     * @param string $code SOAP fault code
     * @param array|null $details Detailed reason message(s)
     * @return string
     */
    public function getSoapFaultMessage($reason, $code, $details = null)
    {
        $detailXml = $this->_generateDetailXml($details);
        $language = $this->getLanguage();
        $detailsNamespace = !empty($detailXml)
            ? 'xmlns:m="' . urlencode($this->_soapServer->generateUri(true)) . '"'
            : '';
        $reason = htmlentities($reason);
        $message = <<<FAULT_MESSAGE
<?xml version="1.0" encoding="utf-8" ?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" $detailsNamespace>
   <env:Body>
      <env:Fault>
         <env:Code>
            <env:Value>env:$code</env:Value>
         </env:Code>
         <env:Reason>
            <env:Text xml:lang="$language">$reason</env:Text>
         </env:Reason>
         $detailXml
      </env:Fault>
   </env:Body>
</env:Envelope>
FAULT_MESSAGE;
        return $message;
    }

    /**
     * Generate 'Detail' node content.
     *
     * In case when fault name is undefined, no 'Detail' node is generated.
     *
     * @param array $details
     * @return string
     */
    protected function _generateDetailXml($details)
    {
        $detailsXml = '';
        if (is_array($details) && !empty($details)) {
            $detailsXml = $this->_convertDetailsToXml($details);
            if ($detailsXml) {
                $errorDetailsNode = self::NODE_DETAIL_WRAPPER;
                $detailsXml = "<env:Detail><m:{$errorDetailsNode}>"
                    . $detailsXml . "</m:{$errorDetailsNode}></env:Detail>";
            } else {
                $detailsXml = '';
            }
        }
        return $detailsXml;
    }

    /**
     * Recursively convert details array into XML structure.
     *
     * @param array $details
     * @return string
     */
    protected function _convertDetailsToXml($details)
    {
        $detailsXml = '';
        foreach ($details as $detailNode => $detailValue) {
            $detailNode = htmlspecialchars($detailNode);
            if (is_numeric($detailNode)) {
                continue;
            }
            switch ($detailNode) {
                case self::NODE_DETAIL_TRACE:
                    if (is_string($detailValue) || is_numeric($detailValue)) {
                        $detailsXml .= "<m:{$detailNode}>" . htmlspecialchars($detailValue) . "</m:{$detailNode}>";
                    }
                    break;
                case self::NODE_DETAIL_PARAMETERS:
                    $detailsXml .= $this->_getParametersXml($detailValue);
                    break;
                case self::NODE_DETAIL_WRAPPED_ERRORS:
                    $detailsXml .= $this->_getWrappedErrorsXml($detailValue);
                    break;
            }
        }
        return $detailsXml;
    }

    /**
     * Generate XML for parameters.
     *
     * @param array $parameters
     * @return string
     */
    protected function _getParametersXml($parameters)
    {
        $result = '';
        if (!is_array($parameters)) {
            return $result;
        }
        $paramsXml = '';
        foreach ($parameters as $parameterName => $parameterValue) {
            if ((is_string($parameterName) || is_numeric($parameterName))
                && (is_string($parameterValue) || is_numeric($parameterValue))
            ) {
                $keyNode = self::NODE_DETAIL_PARAMETER_KEY;
                $valueNode = self::NODE_DETAIL_PARAMETER_VALUE;
                $parameterNode = self::NODE_DETAIL_PARAMETER;
                if (is_numeric($parameterName)) {
                    $parameterName++;
                }
                $paramsXml .= "<m:$parameterNode><m:$keyNode>$parameterName</m:$keyNode><m:$valueNode>"
                    . htmlspecialchars($parameterValue) . "</m:$valueNode></m:$parameterNode>";
            }
        }
        if (!empty($paramsXml)) {
            $parametersNode = self::NODE_DETAIL_PARAMETERS;
            $result = "<m:$parametersNode>" . $paramsXml . "</m:$parametersNode>";
        }

        return $result;
    }

    /**
     * Generate XML for wrapped errors.
     *
     * @param array $wrappedErrors
     * @return string
     */
    protected function _getWrappedErrorsXml($wrappedErrors)
    {
        $result = '';
        if (!is_array($wrappedErrors)) {
            return $result;
        }

        $errorsXml = '';
        foreach ($wrappedErrors as $error) {
            $errorsXml .= $this->_generateErrorNodeXml($error);
        }
        if (!empty($errorsXml)) {
            $wrappedErrorsNode = self::NODE_DETAIL_WRAPPED_ERRORS;
            $result = "<m:$wrappedErrorsNode>" . $errorsXml . "</m:$wrappedErrorsNode>";
        }

        return $result;
    }

    /**
     * Generate XML for a particular error node.
     *
     * @param array $error
     * @return string
     */
    protected function _generateErrorNodeXML($error)
    {
        $wrappedErrorNode = self::NODE_DETAIL_WRAPPED_ERROR;
        $messageNode = self::NODE_DETAIL_WRAPPED_ERROR_MESSAGE;

        $parameters = $error->getParameters();
        $rawMessage = $error->getRawMessage();
        $xml = "<m:$wrappedErrorNode><m:$messageNode>$rawMessage</m:$messageNode>";

        if (!empty($parameters)) {
            $parametersNode = self::NODE_DETAIL_WRAPPED_ERROR_PARAMETERS;
            $xml .= "<m:$parametersNode>";
            foreach ($parameters as $key => $value) {
                $parameterNode = self::NODE_DETAIL_WRAPPED_ERROR_PARAMETER;
                $keyNode = self::NODE_DETAIL_PARAMETER_KEY;
                $valueNode = self::NODE_DETAIL_WRAPPED_ERROR_VALUE;
                $xml .= "<m:$parameterNode>" .
                    "<m:$keyNode>$key</m:$keyNode><m:$valueNode>$value</m:$valueNode>" .
                    "</m:$parameterNode>";
            }
            $xml .= "</m:$parametersNode>";
        }
        $xml .= "</m:$wrappedErrorNode>";

        return $xml;
    }
}
