<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Header\Accept\FieldValuePart;

/**
 * Field Value Part
 *
 *
 * @see        http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.1
 */
class LanguageFieldValuePart extends AbstractFieldValuePart
{
    public function getLanguage()
    {
        return $this->getInternalValues()->typeString;
    }

    public function getPrimaryTag()
    {
        return $this->getInternalValues()->type;
    }

    public function getSubTag()
    {
        return $this->getInternalValues()->subtype;
    }
}
