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
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 22824 2010-08-09 18:59:54Z renanbr $
 */

/**
 * @see Zend_Service_Ebay_Finding_Abstract
 */
#require_once 'Zend/Service/Ebay/Finding/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @uses       Zend_Service_Ebay_Finding_Abstract
 */
abstract class Zend_Service_Ebay_Finding_Response_Abstract extends Zend_Service_Ebay_Finding_Abstract
{
    /**
     * Indicates whether or not errors or warnings were generated during the
     * processing of the request.
     *
     * Applicable values:
     *
     *     Failure
     *     eBay encountered a fatal error during the processing of the request,
     *     causing the request to fail. When a serious application-level error
     *     occurs, the error is returned instead of the business data.
     *
     *     PartialFailure
     *     eBay successfully processed the request, but one or more non-fatal
     *     errors occurred during the processing. For best results, requests
     *     should return without warning messages. Inspect the message details
     *     and resolve any problems before resubmitting the request.
     *
     *     Success
     *     eBay successfully processed the request and the business data is
     *     returned in the response. Note that it is possible for a response to
     *     return Success, but still not contain the expected data in the result.
     *
     *     Warning
     *     The request was successfully processed, but eBay encountered a
     *     non-fatal error during the processing. For best results, requests
     *     should return without warnings. Inspect the warning details and
     *     resolve the problem before resubmitting the request.
     *
     * @var string
     */
    public $ack;

    /**
     * Information regarding an error or warning that occurred when eBay
     * processed the request.
     *
     * Not returned when the ack value is Success. Run-time errors are not
     * reported here.
     *
     * @var Zend_Service_Ebay_Finding_Error_Message
     */
    public $errorMessage;

    /**
     * This value represents the date and time when eBay processed the request.
     *
     * This value is returned in GMT, the ISO 8601 date and time format
     * (YYYY-MM-DDTHH:MM:SS.SSSZ). See the "dateTime" type for information about
     * the time format, and for details on converting to and from the GMT time
     * zone.
     *
     * @var string
     */
    public $timestamp;

    /**
     * The release version that eBay used to process the request.
     *
     * Developer Technical Support may ask you for the version value if you work
     * with them to troubleshoot issues.
     *
     * @var string
     */
    public $version;

    /**
     * @var string
     */
    protected $_operation;

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->ack       = $this->_query(".//$ns:ack[1]", 'string');
        $this->timestamp = $this->_query(".//$ns:timestamp[1]", 'string');
        $this->version   = $this->_query(".//$ns:version[1]", 'string');

        $node = $this->_xPath->query(".//$ns:errorMessage[1]", $this->_dom)->item(0);
        if ($node) {
            /**
             * @see Zend_Service_Ebay_Finding_Error_Message
             */
            #require_once 'Zend/Service/Ebay/Finding/Error/Message.php';
            $this->errorMessage = new Zend_Service_Ebay_Finding_Error_Message($node);
        }
    }

    /**
     * @param  string $operation
     * @return Zend_Service_Ebay_Finding_Response_Abstract Provides a fluent interface
     */
    public function setOperation($operation)
    {
        $this->_operation = (string) $operation;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->_operation;
    }

    /**
     * @param  string|Zend_Config|array $name
     * @param  mixed                    $value
     * @return Zend_Service_Ebay_Finding_Response_Abstract Provides a fluent interface
     */
    public function setOption($name, $value = null)
    {
        if ($name instanceof Zend_Config) {
            $name = $name->toArray();
        }
        if (is_array($name)) {
            $this->_options = $name;
        } else {
            $this->_options[$name] = $value;
        }
        return $this;
    }

    /**
     * @param  string $name
     * @return mixed
     */
    public function getOption($name = null)
    {
        if (null === $name) {
            return $this->_options;
        }
        if (array_key_exists($name, $this->_options)) {
            return $this->_options[$name];
        }
        return null;
    }
}
