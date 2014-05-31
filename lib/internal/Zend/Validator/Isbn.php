<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Validator
 */

namespace Zend\Validator;

/**
 * @category   Zend
 * @package    Zend_Validate
 */
class Isbn extends AbstractValidator
{
    const AUTO    = 'auto';
    const ISBN10  = '10';
    const ISBN13  = '13';
    const INVALID = 'isbnInvalid';
    const NO_ISBN = 'isbnNoIsbn';

    /**
     * Validation failure message template definitions.
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID => "Invalid type given. String or integer expected",
        self::NO_ISBN => "The input is not a valid ISBN number",
    );

    protected $options = array(
        'type'      => self::AUTO, // Allowed type
        'separator' => '',         // Separator character
    );

    /**
     * Detect input format.
     *
     * @return string
     */
    protected function detectFormat()
    {
        // prepare separator and pattern list
        $sep      = quotemeta($this->getSeparator());
        $patterns = array();
        $lengths  = array();
        $type     = $this->getType();

        // check for ISBN-10
        if ($type == self::ISBN10 || $type == self::AUTO) {
            if (empty($sep)) {
                $pattern = '/^[0-9]{9}[0-9X]{1}$/';
                $length  = 10;
            } else {
                $pattern = "/^[0-9]{1,7}[{$sep}]{1}[0-9]{1,7}[{$sep}]{1}[0-9]{1,7}[{$sep}]{1}[0-9X]{1}$/";
                $length  = 13;
            }

            $patterns[$pattern] = self::ISBN10;
            $lengths[$pattern]  = $length;
        }

        // check for ISBN-13
        if ($type == self::ISBN13 || $type == self::AUTO) {
            if (empty($sep)) {
                $pattern = '/^[0-9]{13}$/';
                $length  = 13;
            } else {
                $pattern = "/^[0-9]{1,9}[{$sep}]{1}[0-9]{1,5}[{$sep}]{1}[0-9]{1,9}[{$sep}]{1}[0-9]{1,9}[{$sep}]{1}[0-9]{1}$/";
                $length  = 17;
            }

            $patterns[$pattern] = self::ISBN13;
            $lengths[$pattern]  = $length;
        }

        // check pattern list
        foreach ($patterns as $pattern => $type) {
            if ((strlen($this->getValue()) == $lengths[$pattern]) && preg_match($pattern, $this->getValue())) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Returns true if and only if $value is a valid ISBN.
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value) && !is_int($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $value = (string) $value;
        $this->setValue($value);

        switch ($this->detectFormat()) {
            case self::ISBN10:
                // sum
                $isbn10 = str_replace($this->getSeparator(), '', $value);
                $sum    = 0;
                for ($i = 0; $i < 9; $i++) {
                    $sum += (10 - $i) * $isbn10{$i};
                }

                // checksum
                $checksum = 11 - ($sum % 11);
                if ($checksum == 11) {
                    $checksum = '0';
                } elseif ($checksum == 10) {
                    $checksum = 'X';
                }
                break;

            case self::ISBN13:
                // sum
                $isbn13 = str_replace($this->getSeparator(), '', $value);
                $sum    = 0;
                for ($i = 0; $i < 12; $i++) {
                    if ($i % 2 == 0) {
                        $sum += $isbn13{$i};
                    } else {
                        $sum += 3 * $isbn13{$i};
                    }
                }
                // checksum
                $checksum = 10 - ($sum % 10);
                if ($checksum == 10) {
                    $checksum = '0';
                }
                break;

            default:
                $this->error(self::NO_ISBN);
                return false;
        }

        // validate
        if (substr($this->getValue(), -1) != $checksum) {
            $this->error(self::NO_ISBN);
            return false;
        }
        return true;
    }

    /**
     * Set separator characters.
     *
     * It is allowed only empty string, hyphen and space.
     *
     * @param  string $separator
     * @throws Exception\InvalidArgumentException When $separator is not valid
     * @return Isbn Provides a fluent interface
     */
    public function setSeparator($separator)
    {
        // check separator
        if (!in_array($separator, array('-', ' ', ''))) {
            throw new Exception\InvalidArgumentException('Invalid ISBN separator.');
        }

        $this->options['separator'] = $separator;
        return $this;
    }

    /**
     * Get separator characters.
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->options['separator'];
    }

    /**
     * Set allowed ISBN type.
     *
     * @param  string $type
     * @throws Exception\InvalidArgumentException When $type is not valid
     * @return Isbn Provides a fluent interface
     */
    public function setType($type)
    {
        // check type
        if (!in_array($type, array(self::AUTO, self::ISBN10, self::ISBN13))) {
            throw new Exception\InvalidArgumentException('Invalid ISBN type');
        }

        $this->options['type'] = $type;
        return $this;
    }

    /**
     * Get allowed ISBN type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->options['type'];
    }
}
