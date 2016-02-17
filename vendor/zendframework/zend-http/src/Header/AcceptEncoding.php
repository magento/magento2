<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Header;

use Zend\Http\Header\Accept\FieldValuePart;

/**
 * Accept Encoding Header
 *
 * @see        http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.3
 */
class AcceptEncoding extends AbstractAccept
{
    protected $regexAddType = '#^([a-zA-Z0-9+-]+|\*)$#';

    /**
     * Get field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'Accept-Encoding';
    }

    /**
     * Cast to string
     *
     * @return string
     */
    public function toString()
    {
        return 'Accept-Encoding: ' . $this->getFieldValue();
    }

    /**
     * Add an encoding, with the given priority
     *
     * @param  string $type
     * @param  int|float $priority
     * @return Accept
     */
    public function addEncoding($type, $priority = 1)
    {
        return $this->addType($type, $priority);
    }

    /**
     * Does the header have the requested encoding?
     *
     * @param  string $type
     * @return bool
     */
    public function hasEncoding($type)
    {
        return $this->hasType($type);
    }

    /**
     * Parse the keys contained in the header line
     *
     * @param string $fieldValuePart
     * @return \Zend\Http\Header\Accept\FieldValuePart\EncodingFieldValuePart
     * @see \Zend\Http\Header\AbstractAccept::parseFieldValuePart()
     */
    protected function parseFieldValuePart($fieldValuePart)
    {
        $internalValues = parent::parseFieldValuePart($fieldValuePart);

        return new FieldValuePart\EncodingFieldValuePart($internalValues);
    }
}
