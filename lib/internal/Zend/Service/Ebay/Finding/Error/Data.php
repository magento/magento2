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
 * @version    $Id: Data.php 22791 2010-08-04 16:11:47Z renanbr $
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
class Zend_Service_Ebay_Finding_Error_Data extends Zend_Service_Ebay_Finding_Abstract
{
    /**
     * There are three categories of errors: request errors, application errors,
     * and system errors.
     *
     * @var string
     */
    public $category;

    /**
     * Name of the domain in which the error occurred.
     *
     * Domain values
     *
     * Marketplace: A business or validation error occurred in the service.
     *
     * SOA: An exception occurred in the Service Oriented Architecture (SOA)
     * framework.
     *
     * @var string
     */
    public $domain;

    /**
     * A unique code that identifies the particular error condition that
     * occurred. Your application can use error codes as identifiers in your
     * customized error-handling algorithms.
     *
     * @var integer
     */
    public $errorId;

    /**
     * Unique identifier for an exception associated with an error.
     *
     * @var string
     */
    public $exceptionId;

    /**
     * A detailed description of the condition that caused in the error.
     *
     * @var string
     */
    public $message;

    /**
     * Various warning and error messages return one or more variables that
     * contain contextual information about the error. This is often the field
     * or value that triggered the error.
     *
     * @var string[]
     */
    public $parameter;

    /**
     * Indicates whether the reported problem is fatal (an error) or is
     * less-severe (a warning). Review the error message details for information
     * on the cause.
     *
     * This API throws an exception when a fatal error occurs. Only warning
     * problems can fill this attribute. See more about error parsing at
     * {@Zend_Service_Ebay_Finding::_parseResponse()}.
     *
     * If the request fails and the application is the source of the error (for
     * example, a required element is missing), update the application before
     * you retry the request. If the problem is due to incorrect user data,
     * alert the end-user to the problem and provide the means for them to
     * correct the data. Once the problem in the application or data is
     * resolved, re-send the request to eBay.
     *
     * If the source of the problem is on eBay's side, you can retry the request
     * a reasonable number of times (eBay recommends you try the request twice).
     * If the error persists, contact Developer Technical Support. Once the
     * problem has been resolved, you can resend the request in its original
     * form.
     *
     * If a warning occurs, warning information is returned in addition to the
     * business data. Normally, you do not need to resend the request (as the
     * original request was successful). However, depending on the cause of the
     * warning, you might need to contact the end user, or eBay, to effect a
     * long term solution to the problem.
     *
     * @var string
     */
    public $severity;

    /**
     * Name of the subdomain in which the error occurred.
     *
     * Subdomain values
     *
     * Finding: The error is specific to the Finding service.
     *
     * MarketplaceCommon: The error is common to all Marketplace services.
     *
     * @var string
     */
    public $subdomain;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->category    = $this->_query(".//$ns:category[1]", 'string');
        $this->domain      = $this->_query(".//$ns:domain[1]", 'string');
        $this->errorId     = $this->_query(".//$ns:errorId[1]", 'integer');
        $this->exceptionId = $this->_query(".//$ns:exceptionId[1]", 'string');
        $this->message     = $this->_query(".//$ns:message[1]", 'string');
        $this->parameter   = $this->_query(".//$ns:parameter", 'string', true);
        $this->severity    = $this->_query(".//$ns:severity[1]", 'string');
        $this->subdomain   = $this->_query(".//$ns:subdomain[1]", 'string');

        $this->_attributes['parameter'] = array(
            'name' => $this->_query(".//$ns:parameter/@name", 'string', true)
        );
    }
}
