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
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Response header PHPUnit Constraint
 *
 * @uses       PHPUnit_Framework_Constraint
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Test_PHPUnit_Constraint_ResponseHeader37 extends PHPUnit_Framework_Constraint
{
    /**#@+
     * Assertion type constants
     */
    const ASSERT_RESPONSE_CODE   = 'assertResponseCode';
    const ASSERT_HEADER          = 'assertHeader';
    const ASSERT_HEADER_CONTAINS = 'assertHeaderContains';
    const ASSERT_HEADER_REGEX    = 'assertHeaderRegex';
    /**#@-*/

    /**
     * Current assertion type
     * @var string
     */
    protected $_assertType      = null;

    /**
     * Available assertion types
     * @var array
     */
    protected $_assertTypes     = array(
        self::ASSERT_RESPONSE_CODE,
        self::ASSERT_HEADER,
        self::ASSERT_HEADER_CONTAINS,
        self::ASSERT_HEADER_REGEX,
    );

    /**
     * @var int Response code
     */
    protected $_code              = 200;

    /**
     * @var int Actual response code
     */
    protected $_actualCode        = null;

    /**
     * @var string Header
     */
    protected $_header            = null;

    /**
     * @var string pattern against which to compare header content
     */
    protected $_match             = null;

    /**
     * Whether or not assertion is negated
     * @var bool
     */
    protected $_negate            = false;

    /**
     * Constructor; setup constraint state
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Indicate negative match
     *
     * @param  bool $flag
     * @return void
     */
    public function setNegate($flag = true)
    {
        $this->_negate = $flag;
    }

    /**
     * Evaluate an object to see if it fits the constraints
     *
     * @param  object       of Zend_Controller_Response_Abstract to be evaluated
     * @param  null|string  Assertion type
     * @param  int|string   HTTP response code to evaluate against | header string (haystack)
     * @param  string       (optional) match (needle), may be required depending on assertion type
     * @return bool
     * NOTE:
     * Drastic changes up to PHPUnit 3.5.15 this was:
     *     public function evaluate($other, $assertType = null)
     * In PHPUnit 3.6.0 they changed the interface into this:
     *     public function evaluate($other, $description = '', $returnResult = FALSE)
     * We use the new interface for PHP-strict checking, but emulate the old one
     */
    public function evaluate($response, $assertType = '', $variable = FALSE)
    {
        if (!$response instanceof Zend_Controller_Response_Abstract) {
            #require_once 'Zend/Test/PHPUnit/Constraint/Exception.php';
            throw new Zend_Test_PHPUnit_Constraint_Exception('Header constraint assertions require a response object');
        }

        if (strstr($assertType, 'Not')) {
            $this->setNegate(true);
            $assertType = str_replace('Not', '', $assertType);
        }

        if (!in_array($assertType, $this->_assertTypes)) {
            #require_once 'Zend/Test/PHPUnit/Constraint/Exception.php';
            throw new Zend_Test_PHPUnit_Constraint_Exception(sprintf('Invalid assertion type "%s" provided to %s constraint', $assertType, __CLASS__));
        }

        $this->_assertType = $assertType;

        $argv     = func_get_args();
        $argc     = func_num_args();

        switch ($assertType) {
            case self::ASSERT_RESPONSE_CODE:
                if (3 > $argc) {
                    #require_once 'Zend/Test/PHPUnit/Constraint/Exception.php';
                    throw new Zend_Test_PHPUnit_Constraint_Exception('No response code provided against which to match');
                }
                $this->_code = $code = $argv[2];
                return ($this->_negate)
                    ? $this->_notCode($response, $code)
                    : $this->_code($response, $code);
            case self::ASSERT_HEADER:
                if (3 > $argc) {
                    #require_once 'Zend/Test/PHPUnit/Constraint/Exception.php';
                    throw new Zend_Test_PHPUnit_Constraint_Exception('No header provided against which to match');
                }
                $this->_header = $header = $argv[2];
                return ($this->_negate)
                    ? $this->_notHeader($response, $header)
                    : $this->_header($response, $header);
            case self::ASSERT_HEADER_CONTAINS:
                if (4 > $argc) {
                    #require_once 'Zend/Test/PHPUnit/Constraint/Exception.php';
                    throw new Zend_Test_PHPUnit_Constraint_Exception('Both a header name and content to match are required for ' . $assertType);
                }
                $this->_header = $header = $argv[2];
                $this->_match  = $match  = $argv[3];
                return ($this->_negate)
                    ? $this->_notHeaderContains($response, $header, $match)
                    : $this->_headerContains($response, $header, $match);
            case self::ASSERT_HEADER_REGEX:
                if (4 > $argc) {
                    #require_once 'Zend/Test/PHPUnit/Constraint/Exception.php';
                    throw new Zend_Test_PHPUnit_Constraint_Exception('Both a header name and content to match are required for ' . $assertType);
                }
                $this->_header = $header = $argv[2];
                $this->_match  = $match  = $argv[3];
                return ($this->_negate)
                    ? $this->_notHeaderRegex($response, $header, $match)
                    : $this->_headerRegex($response, $header, $match);
            default:
                #require_once 'Zend/Test/PHPUnit/Constraint/Exception.php';
                throw new Zend_Test_PHPUnit_Constraint_Exception('Invalid assertion type ' . $assertType);
        }
    }

    /**
     * Report Failure
     *
     * @see    PHPUnit_Framework_Constraint for implementation details
     * @param  mixed    CSS selector path
     * @param  string   Failure description
     * @param  object   Cannot be used, null
     * @return void
     * @throws PHPUnit_Framework_ExpectationFailedException
     * NOTE:
     * Drastic changes up to PHPUnit 3.5.15 this was:
     *     public function fail($other, $description, $not = false)
     * In PHPUnit 3.6.0 they changed the interface into this:
     *     protected function fail($other, $description, PHPUnit_Framework_ComparisonFailure $comparisonFailure = NULL)
     * We use the new interface for PHP-strict checking
     */
    public function fail($other, $description, PHPUnit_Framework_ComparisonFailure $cannot_be_used = NULL)
    {
        #require_once 'Zend/Test/PHPUnit/Constraint/Exception.php';
        switch ($this->_assertType) {
            case self::ASSERT_RESPONSE_CODE:
                $failure = 'Failed asserting response code "%s"';
                if ($this->_negate) {
                    $failure = 'Failed asserting response code IS NOT "%s"';
                }
                $failure = sprintf($failure, $this->_code);
                if (!$this->_negate && $this->_actualCode) {
                    $failure .= sprintf(PHP_EOL . 'Was "%s"', $this->_actualCode);
                }
                break;
            case self::ASSERT_HEADER:
                $failure = 'Failed asserting response header "%s" found';
                if ($this->_negate) {
                    $failure = 'Failed asserting response response header "%s" WAS NOT found';
                }
                $failure = sprintf($failure, $this->_header);
                break;
            case self::ASSERT_HEADER_CONTAINS:
                $failure = 'Failed asserting response header "%s" exists and contains "%s"';
                if ($this->_negate) {
                    $failure = 'Failed asserting response header "%s" DOES NOT CONTAIN "%s"';
                }
                $failure = sprintf($failure, $this->_header, $this->_match);
                break;
            case self::ASSERT_HEADER_REGEX:
                $failure = 'Failed asserting response header "%s" exists and matches regex "%s"';
                if ($this->_negate) {
                    $failure = 'Failed asserting response header "%s" DOES NOT MATCH regex "%s"';
                }
                $failure = sprintf($failure, $this->_header, $this->_match);
                break;
            default:
                throw new Zend_Test_PHPUnit_Constraint_Exception('Invalid assertion type ' . __FUNCTION__);
        }

        if (!empty($description)) {
            $failure = $description . "\n" . $failure;
        }

        throw new Zend_Test_PHPUnit_Constraint_Exception($failure);
    }

    /**
     * Complete implementation
     *
     * @return string
     */
    public function toString()
    {
        return '';
    }

    /**
     * Compare response code for positive match
     *
     * @param  Zend_Controller_Response_Abstract $response
     * @param  int $code
     * @return bool
     */
    protected function _code(Zend_Controller_Response_Abstract $response, $code)
    {
        $test = $this->_getCode($response);
        $this->_actualCode = $test;
        return ($test == $code);
    }

    /**
     * Compare response code for negative match
     *
     * @param  Zend_Controller_Response_Abstract $response
     * @param  int $code
     * @return bool
     */
    protected function _notCode(Zend_Controller_Response_Abstract $response, $code)
    {
        $test = $this->_getCode($response);
        return ($test != $code);
    }

    /**
     * Retrieve response code
     *
     * @param  Zend_Controller_Response_Abstract $response
     * @return int
     */
    protected function _getCode(Zend_Controller_Response_Abstract $response)
    {
        $test = $response->getHttpResponseCode();
        if (null === $test) {
            $test = 200;
        }
        return $test;
    }

    /**
     * Positive check for response header presence
     *
     * @param  Zend_Controller_Response_Abstract $response
     * @param  string $header
     * @return bool
     */
    protected function _header(Zend_Controller_Response_Abstract $response, $header)
    {
        return (null !== $this->_getHeader($response, $header));
    }

    /**
     * Negative check for response header presence
     *
     * @param  Zend_Controller_Response_Abstract $response
     * @param  string $header
     * @return bool
     */
    protected function _notHeader(Zend_Controller_Response_Abstract $response, $header)
    {
        return (null === $this->_getHeader($response, $header));
    }

    /**
     * Retrieve response header
     *
     * @param  Zend_Controller_Response_Abstract $response
     * @param  string $header
     * @return string|null
     */
    protected function _getHeader(Zend_Controller_Response_Abstract $response, $header)
    {
        $headers = $response->sendHeaders();
        $header  = strtolower($header);
        if (array_key_exists($header, $headers)) {
            return $headers[$header];
        }
        return null;
    }

    /**
     * Positive check for header contents matching pattern
     *
     * @param  Zend_Controller_Response_Abstract $response
     * @param  string $header
     * @param  string $match
     * @return bool
     */
    protected function _headerContains(Zend_Controller_Response_Abstract $response, $header, $match)
    {
        if (null === ($fullHeader = $this->_getHeader($response, $header))) {
            return false;
        }

        $contents = str_replace($header . ': ', '', $fullHeader);

        return (strstr($contents, $match) !== false);
    }

    /**
     * Negative check for header contents matching pattern
     *
     * @param  Zend_Controller_Response_Abstract $response
     * @param  string $header
     * @param  string $match
     * @return bool
     */
    protected function _notHeaderContains(Zend_Controller_Response_Abstract $response, $header, $match)
    {
        if (null === ($fullHeader = $this->_getHeader($response, $header))) {
            return true;
        }

        $contents = str_replace($header . ': ', '', $fullHeader);

        return (strstr($contents, $match) === false);
    }

    /**
     * Positive check for header contents matching regex
     *
     * @param  Zend_Controller_Response_Abstract $response
     * @param  string $header
     * @param  string $pattern
     * @return bool
     */
    protected function _headerRegex(Zend_Controller_Response_Abstract $response, $header, $pattern)
    {
        if (null === ($fullHeader = $this->_getHeader($response, $header))) {
            return false;
        }

        $contents = str_replace($header . ': ', '', $fullHeader);

        return preg_match($pattern, $contents);
    }

    /**
     * Negative check for header contents matching regex
     *
     * @param  Zend_Controller_Response_Abstract $response
     * @param  string $header
     * @param  string $pattern
     * @return bool
     */
    protected function _notHeaderRegex(Zend_Controller_Response_Abstract $response, $header, $pattern)
    {
        if (null === ($fullHeader = $this->_getHeader($response, $header))) {
            return true;
        }

        $contents = str_replace($header . ': ', '', $fullHeader);

        return !preg_match($pattern, $contents);
    }
}
