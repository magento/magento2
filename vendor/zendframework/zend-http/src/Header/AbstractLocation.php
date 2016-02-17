<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Header;

use Zend\Uri\Exception as UriException;
use Zend\Uri\UriFactory;
use Zend\Uri\UriInterface;

/**
 * Abstract Location Header
 * Supports headers that have URI as value
 * @see Zend\Http\Header\Location
 * @see Zend\Http\Header\ContentLocation
 * @see Zend\Http\Header\Referer
 *
 * Note for 'Location' header:
 * While RFC 1945 requires an absolute URI, most of the browsers also support relative URI
 * This class allows relative URIs, and let user retrieve URI instance if strict validation needed
 */
abstract class AbstractLocation implements HeaderInterface
{
    /**
     * URI for this header
     *
     * @var UriInterface
     */
    protected $uri = null;

    /**
     * Create location-based header from string
     *
     * @param string $headerLine
     * @return AbstractLocation
     * @throws Exception\InvalidArgumentException
     */
    public static function fromString($headerLine)
    {
        $locationHeader = new static();

        // ZF-5520 - IIS bug, no space after colon
        list($name, $uri) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== strtolower($locationHeader->getFieldName())) {
            throw new Exception\InvalidArgumentException(
                'Invalid header line for "' . $locationHeader->getFieldName() . '" header string'
            );
        }

        HeaderValue::assertValid($uri);
        $locationHeader->setUri(trim($uri));

        return $locationHeader;
    }

    /**
     * Set the URI/URL for this header, this can be a string or an instance of Zend\Uri\Http
     *
     * @param string|UriInterface $uri
     * @return AbstractLocation
     * @throws Exception\InvalidArgumentException
     */
    public function setUri($uri)
    {
        if (is_string($uri)) {
            try {
                $uri = UriFactory::factory($uri);
            } catch (UriException\InvalidUriPartException $e) {
                throw new Exception\InvalidArgumentException(
                    sprintf('Invalid URI passed as string (%s)', (string) $uri),
                    $e->getCode(),
                    $e
                );
            }
        } elseif (!($uri instanceof UriInterface)) {
            throw new Exception\InvalidArgumentException('URI must be an instance of Zend\Uri\Http or a string');
        }
        $this->uri = $uri;

        return $this;
    }

    /**
     * Return the URI for this header
     *
     * @return string
     */
    public function getUri()
    {
        if ($this->uri instanceof UriInterface) {
            return $this->uri->toString();
        }
        return $this->uri;
    }

    /**
     * Return the URI for this header as an instance of Zend\Uri\Http
     *
     * @return UriInterface
     */
    public function uri()
    {
        if ($this->uri === null || is_string($this->uri)) {
            $this->uri = UriFactory::factory($this->uri);
        }
        return $this->uri;
    }

    /**
     * Get header value as URI string
     *
     * @return string
     */
    public function getFieldValue()
    {
        return $this->getUri();
    }

    /**
     * Output header line
     *
     * @return string
     */
    public function toString()
    {
        return $this->getFieldName() . ': ' . $this->getUri();
    }

    /**
     * Allow casting to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
