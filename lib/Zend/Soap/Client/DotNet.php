<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage Client
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: DotNet.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Soap_Client */
#require_once 'Zend/Soap/Client.php';

if (extension_loaded('soap')) {

/**
 * Zend_Soap_Client_Local
 *
 * Class is intended to be used with .Net Web Services.
 *
 * Important! Class is at experimental stage now.
 * Please leave your notes, compatiblity issues reports or
 * suggestions in fw-webservices@lists.zend.com or fw-general@lists.com
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage Client
 */
class Zend_Soap_Client_DotNet extends Zend_Soap_Client
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
     * @throws Zend_Soap_Client_Exception
     */
    protected function _preProcessArguments($arguments)
    {
        if (count($arguments) > 1  ||
            (count($arguments) == 1  &&  !is_array(reset($arguments)))
           ) {
            #require_once 'Zend/Soap/Client/Exception.php';
            throw new Zend_Soap_Client_Exception('.Net webservice arguments have to be grouped into array: array(\'a\' => $a, \'b\' => $b, ...).');
        }

        // Do nothing
        return $arguments;
    }

    /**
     * Perform result pre-processing
     *
     * My be overridden in descendant classes
     *
     * @param array $arguments
     */
    protected function _preProcessResult($result)
    {
        $resultProperty = $this->getLastMethod() . 'Result';

        return $result->$resultProperty;
    }

}

} // end if (extension_loaded('soap')
