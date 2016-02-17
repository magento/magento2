<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Header;

use Zend\Uri\UriFactory;

/**
 * @throws Exception\InvalidArgumentException
 * @see http://tools.ietf.org/id/draft-abarth-origin-03.html#rfc.section.2
 */
class Origin implements HeaderInterface
{
    /**
     * @var string
     */
    protected $value = '';

    public static function fromString($headerLine)
    {
        list($name, $value) = explode(': ', $headerLine, 2);

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'origin') {
            throw new Exception\InvalidArgumentException('Invalid header line for Origin string: "' . $name . '"');
        }

        $uri = UriFactory::factory($value);
        if (!$uri->isValid()) {
            throw new Exception\InvalidArgumentException('Invalid header value for Origin key: "' . $name . '"');
        }

        return new static($value);
    }

    /**
     * @param string|null $value
     */
    public function __construct($value = null)
    {
        if ($value) {
            HeaderValue::assertValid($value);
            $this->value = $value;
        }
    }

    public function getFieldName()
    {
        return 'Origin';
    }

    public function getFieldValue()
    {
        return $this->value;
    }

    public function toString()
    {
        return 'Origin: ' . $this->getFieldValue();
    }
}
