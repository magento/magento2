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
 * Accept Language Header
 *
 * @see        http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
 */
class AcceptLanguage extends AbstractAccept
{
    protected $regexAddType = '#^([a-zA-Z0-9+-]+|\*)$#';

    /**
     * Get field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'Accept-Language';
    }

    /**
     * Cast to string
     *
     * @return string
     */
    public function toString()
    {
        return 'Accept-Language: ' . $this->getFieldValue();
    }

    /**
     * Add a language, with the given priority
     *
     * @param  string $type
     * @param  int|float $priority
     * @return Accept
     */
    public function addLanguage($type, $priority = 1)
    {
        return $this->addType($type, $priority);
    }

    /**
     * Does the header have the requested language?
     *
     * @param  string $type
     * @return bool
     */
    public function hasLanguage($type)
    {
        return $this->hasType($type);
    }

    /**
     * Parse the keys contained in the header line
     *
     * @param string $fieldValuePart
     * @return \Zend\Http\Header\Accept\FieldValuePart\LanguageFieldValuePart
     * @see \Zend\Http\Header\AbstractAccept::parseFieldValuePart()
     */
    protected function parseFieldValuePart($fieldValuePart)
    {
        $raw = $fieldValuePart;
        if ($pos = strpos($fieldValuePart, '-')) {
            $type = trim(substr($fieldValuePart, 0, $pos));
        } else {
            $type = trim(substr($fieldValuePart, 0));
        }

        $params = $this->getParametersFromFieldValuePart($fieldValuePart);

        if ($pos = strpos($fieldValuePart, ';')) {
            $fieldValuePart = $type = trim(substr($fieldValuePart, 0, $pos));
        }

        if (strpos($fieldValuePart, '-')) {
            $subtypeWhole = $format = $subtype = trim(substr($fieldValuePart, strpos($fieldValuePart, '-')+1));
        } else {
            $subtypeWhole = '';
            $format = '*';
            $subtype = '*';
        }

        $aggregated = array(
                'typeString' => trim($fieldValuePart),
                'type'       => $type,
                'subtype'    => $subtype,
                'subtypeRaw' => $subtypeWhole,
                'format'     => $format,
                'priority'   => isset($params['q']) ? $params['q'] : 1,
                'params'     => $params,
                'raw'        => trim($raw)
        );

        return new FieldValuePart\LanguageFieldValuePart((object) $aggregated);
    }
}
