<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Soap
 */

namespace Zend\Soap\Client;

use Zend\Soap\Client as SOAPClient;
use Zend\Soap\Exception;

/**
 * .NET SOAP client
 *
 * Class is intended to be used with .Net Web Services.
 *
 * Important! Class is at experimental stage now.
 * Please leave your notes, compatibility issues reports or
 * suggestions in fw-webservices@lists.zend.com or fw-general@lists.com
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage Client
 */
class DotNet extends SOAPClient
{
    /**
     * Constructor
     *
     * @param string $wsdl
     * @param array $options
     */
    public function __construct($wsdl = null, $options = null)
    {
        // Use SOAP 1.1 as default
        $this->setSoapVersion(SOAP_1_1);

        parent::__construct($wsdl, $options);
    }


    /**
     * Perform arguments pre-processing
     *
     * My be overridden in descendant classes
     *
     * @param array $arguments
     * @throws Exception\RuntimeException
     * @return array
     */
    protected function _preProcessArguments($arguments)
    {
        if (count($arguments) > 1  ||
            (count($arguments) == 1  &&  !is_array(reset($arguments)))
           ) {
            throw new Exception\RuntimeException('.Net webservice arguments have to be grouped into array: array(\'a\' => $a, \'b\' => $b, ...).');
        }

        // Do nothing
        return $arguments;
    }

    /**
     * Perform result pre-processing
     *
     * My be overridden in descendant classes
     *
     * @param object $result
     * @return mixed
     */
    protected function _preProcessResult($result)
    {
        $resultProperty = $this->getLastMethod() . 'Result';

        return $result->$resultProperty;
    }

}
