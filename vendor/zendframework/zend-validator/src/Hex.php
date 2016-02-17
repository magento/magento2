<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

class Hex extends AbstractValidator
{
    const INVALID = 'hexInvalid';
    const NOT_HEX = 'notHex';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID => "Invalid type given. String expected",
        self::NOT_HEX => "The input contains non-hexadecimal characters",
    );

    /**
     * Returns true if and only if $value contains only hexadecimal digit characters
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_string($value) && !is_int($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);
        if (!ctype_xdigit((string) $value)) {
            $this->error(self::NOT_HEX);
            return false;
        }

        return true;
    }
}
