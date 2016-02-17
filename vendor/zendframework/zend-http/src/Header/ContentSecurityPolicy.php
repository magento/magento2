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
 * Content Security Policy 1.0 Header
 *
 * @link http://www.w3.org/TR/CSP/
 */
class ContentSecurityPolicy implements HeaderInterface
{
    /**
     * Valid directive names
     *
     * @var array
     */
    protected $validDirectiveNames = array(
        // As per http://www.w3.org/TR/CSP/#directives
        'default-src',
        'script-src',
        'object-src',
        'style-src',
        'img-src',
        'media-src',
        'frame-src',
        'font-src',
        'connect-src',
        'sandbox',
        'report-uri',
    );

    /**
     * The directives defined for this policy
     *
     * @var array
     */
    protected $directives = array();

    /**
     * Get the list of defined directives
     *
     * @return array
     */
    public function getDirectives()
    {
        return $this->directives;
    }

    /**
     * Sets the directive to consist of the source list
     *
     * Reverses http://www.w3.org/TR/CSP/#parsing-1
     *
     * @param string $name The directive name.
     * @param array $sources The source list.
     * @return self
     * @throws Exception\InvalidArgumentException If the name is not a valid directive name.
     */
    public function setDirective($name, array $sources)
    {
        if (! in_array($name, $this->validDirectiveNames, true)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a valid directive name; received "%s"',
                __METHOD__,
                (string) $name
            ));
        }
        if (empty($sources)) {
            $this->directives[$name] = "'none'";
            return $this;
        }

        array_walk($sources, array(__NAMESPACE__ . '\HeaderValue', 'assertValid'));

        $this->directives[$name] = implode(' ', $sources);
        return $this;
    }

    /**
     * Create Content Security Policy header from a given header line
     *
     * @param string $headerLine The header line to parse.
     * @return self
     * @throws Exception\InvalidArgumentException If the name field in the given header line does not match.
     */
    public static function fromString($headerLine)
    {
        $header = new static();
        $headerName = $header->getFieldName();
        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);
        // Ensure the proper header name
        if (strcasecmp($name, $headerName) != 0) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid header line for %s string: "%s"',
                $headerName,
                $name
            ));
        }
        // As per http://www.w3.org/TR/CSP/#parsing
        $tokens = explode(';', $value);
        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token) {
                list($directiveName, $directiveValue) = explode(' ', $token, 2);
                if (!isset($header->directives[$directiveName])) {
                    $header->setDirective($directiveName, array($directiveValue));
                }
            }
        }
        return $header;
    }

    /**
     * Get the header name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'Content-Security-Policy';
    }

    /**
     * Get the header value
     *
     * @return string
     */
    public function getFieldValue()
    {
        $directives = array();
        foreach ($this->directives as $name => $value) {
            $directives[] = sprintf('%s %s;', $name, $value);
        }
        return implode(' ', $directives);
    }

    /**
     * Return the header as a string
     *
     * @return string
     */
    public function toString()
    {
        return sprintf('%s: %s', $this->getFieldName(), $this->getFieldValue());
    }
}
