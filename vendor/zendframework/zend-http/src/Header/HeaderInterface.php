<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Header;

/**
 * Interface for HTTP Header classes.
 */
interface HeaderInterface
{
    /**
     * Factory to generate a header object from a string
     *
     * @param string $headerLine
     * @return self
     * @throws Exception\InvalidArgumentException If the header does not match RFC 2616 definition.
     * @see http://tools.ietf.org/html/rfc2616#section-4.2
     */
    public static function fromString($headerLine);

    /**
     * Retrieve header name
     *
     * @return string
     */
    public function getFieldName();

    /**
     * Retrieve header value
     *
     * @return string
     */
    public function getFieldValue();

    /**
     * Cast to string
     *
     * Returns in form of "NAME: VALUE"
     *
     * @return string
     */
    public function toString();
}
