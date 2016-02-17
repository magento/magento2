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
 * @throws Exception\InvalidArgumentException
 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.47
 */
class WWWAuthenticate implements MultipleHeaderInterface
{
    /**
     * @var string
     */
    protected $value;

    public static function fromString($headerLine)
    {
        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'www-authenticate') {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid header line for WWW-Authenticate string: "%s"',
                $name
            ));
        }

        // @todo implementation details
        $header = new static($value);

        return $header;
    }

    public function __construct($value = null)
    {
        if ($value) {
            HeaderValue::assertValid($value);
            $this->value = $value;
        }
    }

    public function getFieldName()
    {
        return 'WWW-Authenticate';
    }

    public function getFieldValue()
    {
        return $this->value;
    }

    public function toString()
    {
        return 'WWW-Authenticate: ' . $this->getFieldValue();
    }

    public function toStringMultipleHeaders(array $headers)
    {
        $strings = array($this->toString());
        foreach ($headers as $header) {
            if (!$header instanceof WWWAuthenticate) {
                throw new Exception\RuntimeException(
                    'The WWWAuthenticate multiple header implementation can only'
                    . ' accept an array of WWWAuthenticate headers'
                );
            }
            $strings[] = $header->toString();
        }
        return implode("\r\n", $strings);
    }
}
