<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\Validator;

use Locale;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\AbstractValidator;

class PhoneNumber extends AbstractValidator
{
    const NO_MATCH    = 'phoneNumberNoMatch';
    const UNSUPPORTED = 'phoneNumberUnsupported';
    const INVALID     = 'phoneNumberInvalid';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::NO_MATCH    => 'The input does not match a phone number format',
        self::UNSUPPORTED => 'The country provided is currently unsupported',
        self::INVALID     => 'Invalid type given. String expected',
    );

    /**
     * Phone Number Patterns
     *
     * @link http://code.google.com/p/libphonenumber/source/browse/trunk/resources/PhoneNumberMetadata.xml
     * @var array
     */
    protected static $phone = array();

    /**
     * ISO 3611 Country Code
     *
     * @var string
     */
    protected $country;

    /**
     * Allow Possible Matches
     *
     * @var bool
     */
    protected $allowPossible = false;

    /**
     * Allowed Types
     *
     * @var array
     */
    protected $allowedTypes = array(
        'general',
        'fixed',
        'tollfree',
        'personal',
        'mobile',
        'voip',
        'uan',
    );

    /**
     * Constructor for the PhoneNumber validator
     *
     * Options
     * - country | string | field or value
     * - allowed_types | array | array of allowed types
     * - allow_possible | boolean | allow possible matches aka non-strict
     *
     * @param array|Traversable $options
     */
    public function __construct($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (array_key_exists('country', $options)) {
            $this->setCountry($options['country']);
        } else {
            $country = Locale::getRegion(Locale::getDefault());
            $this->setCountry($country);
        }

        if (array_key_exists('allowed_types', $options)) {
            $this->allowedTypes($options['allowed_types']);
        }

        if (array_key_exists('allow_possible', $options)) {
            $this->allowPossible($options['allow_possible']);
        }

        parent::__construct($options);
    }

    /**
     * Allowed Types
     *
     * @param  array|null $types
     * @return self|array
     */
    public function allowedTypes(array $types = null)
    {
        if (null !== $types) {
            $this->allowedTypes = $types;

            return $this;
        }

        return $this->allowedTypes;
    }

    /**
     * Allow Possible
     *
     * @param  bool|null $possible
     * @return self|bool
     */
    public function allowPossible($possible = null)
    {
        if (null !== $possible) {
            $this->allowPossible = (bool) $possible;

            return $this;
        }

        return $this->allowPossible;
    }

    /**
     * Get Country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set Country
     *
     * @param  string $country
     * @return self
     */
    public function setCountry($country)
    {
        $this->country = strtoupper($country);

        return $this;
    }

    /**
     * Load Pattern
     *
     * @param  string        $code
     * @return array[]|false
     */
    protected function loadPattern($code)
    {
        if (!isset(static::$phone[$code])) {
            if (!preg_match('/^[A-Z]{2}$/D', $code)) {
                return false;
            }

            $file = __DIR__ . '/PhoneNumber/' . $code . '.php';
            if (!file_exists($file)) {
                return false;
            }

            static::$phone[$code] = include $file;
        }

        return static::$phone[$code];
    }

    /**
     * Returns true if and only if $value matches phone number format
     *
     * @param  string $value
     * @param  array  $context
     * @return bool
     */
    public function isValid($value = null, $context = null)
    {
        if (!is_scalar($value)) {
            $this->error(self::INVALID);

            return false;
        }
        $this->setValue($value);

        $country = $this->getCountry();

        if (!$countryPattern = $this->loadPattern($country)) {
            if (isset($context[$country])) {
                $country = $context[$country];
            }

            if (!$countryPattern = $this->loadPattern($country)) {
                $this->error(self::UNSUPPORTED);

                return false;
            }
        }

        $codeLength = strlen($countryPattern['code']);

        /*
         * Check for existence of either:
         *   1) E.123/E.164 international prefix
         *   2) International double-O prefix
         *   3) Bare country prefix
         */
        if (('+' . $countryPattern['code']) == substr($value, 0, $codeLength + 1)) {
            $valueNoCountry = substr($value, $codeLength + 1);
        } elseif (('00' . $countryPattern['code']) == substr($value, 0, $codeLength + 2)) {
            $valueNoCountry = substr($value, $codeLength + 2);
        } elseif ($countryPattern['code'] == substr($value, 0, $codeLength)) {
            $valueNoCountry = substr($value, $codeLength);
        }

        // check against allowed types strict match:
        foreach ($countryPattern['patterns']['national'] as $type => $pattern) {
            if (in_array($type, $this->allowedTypes)) {
                // check pattern:
                if (preg_match($pattern, $value)) {
                    return true;
                } elseif (isset($valueNoCountry) && preg_match($pattern, $valueNoCountry)) {
                    // this handles conditions where the country code and prefix are the same
                    return true;
                }
            }
        }

        // check for possible match:
        if ($this->allowPossible()) {
            foreach ($countryPattern['patterns']['possible'] as $type => $pattern) {
                if (in_array($type, $this->allowedTypes)) {
                    // check pattern:
                    if (preg_match($pattern, $value)) {
                        return true;
                    } elseif (isset($valueNoCountry) && preg_match($pattern, $valueNoCountry)) {
                        // this handles conditions where the country code and prefix are the same
                        return true;
                    }
                }
            }
        }

        $this->error(self::NO_MATCH);

        return false;
    }
}
