<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter;

use Traversable;
use Zend\Stdlib\ArrayUtils;

class Boolean extends AbstractFilter
{
    const TYPE_BOOLEAN        = 1;
    const TYPE_INTEGER        = 2;
    const TYPE_FLOAT          = 4;
    const TYPE_STRING         = 8;
    const TYPE_ZERO_STRING    = 16;
    const TYPE_EMPTY_ARRAY    = 32;
    const TYPE_NULL           = 64;
    const TYPE_PHP            = 127;
    const TYPE_FALSE_STRING   = 128;
    const TYPE_LOCALIZED      = 256;
    const TYPE_ALL            = 511;

    /**
     * @var array
     */
    protected $constants = array(
        self::TYPE_BOOLEAN       => 'boolean',
        self::TYPE_INTEGER       => 'integer',
        self::TYPE_FLOAT         => 'float',
        self::TYPE_STRING        => 'string',
        self::TYPE_ZERO_STRING   => 'zero',
        self::TYPE_EMPTY_ARRAY   => 'array',
        self::TYPE_NULL          => 'null',
        self::TYPE_PHP           => 'php',
        self::TYPE_FALSE_STRING  => 'false',
        self::TYPE_LOCALIZED     => 'localized',
        self::TYPE_ALL           => 'all',
    );

    /**
     * @var array
     */
    protected $options = array(
        'type'         => self::TYPE_PHP,
        'casting'      => true,
        'translations' => array(),
    );

    /**
     * Constructor
     *
     * @param array|Traversable|int|null  $typeOrOptions
     * @param bool  $casting
     * @param array $translations
     */
    public function __construct($typeOrOptions = null, $casting = true, $translations = array())
    {
        if ($typeOrOptions !== null) {
            if ($typeOrOptions instanceof Traversable) {
                $typeOrOptions = ArrayUtils::iteratorToArray($typeOrOptions);
            }

            if (is_array($typeOrOptions)) {
                if (isset($typeOrOptions['type']) || isset($typeOrOptions['casting']) || isset($typeOrOptions['translations'])) {
                    $this->setOptions($typeOrOptions);
                } else {
                    $this->setType($typeOrOptions);
                    $this->setCasting($casting);
                    $this->setTranslations($translations);
                }
            } else {
                $this->setType($typeOrOptions);
                $this->setCasting($casting);
                $this->setTranslations($translations);
            }
        }
    }

    /**
     * Set boolean types
     *
     * @param  int|array $type
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function setType($type = null)
    {
        if (is_array($type)) {
            $detected = 0;
            foreach ($type as $value) {
                if (is_int($value)) {
                    $detected += $value;
                } elseif (in_array($value, $this->constants)) {
                    $detected += array_search($value, $this->constants);
                }
            }

            $type = $detected;
        } elseif (is_string($type) && in_array($type, $this->constants)) {
            $type = array_search($type, $this->constants);
        }

        if (!is_int($type) || ($type < 0) || ($type > self::TYPE_ALL)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Unknown type value "%s" (%s)',
                $type,
                gettype($type)
            ));
        }

        $this->options['type'] = $type;
        return $this;
    }

    /**
     * Returns defined boolean types
     *
     * @return int
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * Set the working mode
     *
     * @param  bool $flag When true this filter works like cast
     *                       When false it recognises only true and false
     *                       and all other values are returned as is
     * @return self
     */
    public function setCasting($flag = true)
    {
        $this->options['casting'] = (bool) $flag;
        return $this;
    }

    /**
     * Returns the casting option
     *
     * @return bool
     */
    public function getCasting()
    {
        return $this->options['casting'];
    }

    /**
     * @param  array|Traversable $translations
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function setTranslations($translations)
    {
        if (!is_array($translations) && !$translations instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '"%s" expects an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($translations) ? get_class($translations) : gettype($translations))
            ));
        }

        foreach ($translations as $message => $flag) {
            $this->options['translations'][$message] = (bool) $flag;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getTranslations()
    {
        return $this->options['translations'];
    }

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Returns a boolean representation of $value
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $type    = $this->getType();
        $casting = $this->getCasting();

        // LOCALIZED
        if ($type >= self::TYPE_LOCALIZED) {
            $type -= self::TYPE_LOCALIZED;
            if (is_string($value)) {
                if (isset($this->options['translations'][$value])) {
                    return (bool) $this->options['translations'][$value];
                }
            }
        }

        // FALSE_STRING ('false')
        if ($type >= self::TYPE_FALSE_STRING) {
            $type -= self::TYPE_FALSE_STRING;
            if (is_string($value) && (strtolower($value) == 'false')) {
                return false;
            }

            if (!$casting && is_string($value) && (strtolower($value) == 'true')) {
                return true;
            }
        }

        // NULL (null)
        if ($type >= self::TYPE_NULL) {
            $type -= self::TYPE_NULL;
            if ($value === null) {
                return false;
            }
        }

        // EMPTY_ARRAY (array())
        if ($type >= self::TYPE_EMPTY_ARRAY) {
            $type -= self::TYPE_EMPTY_ARRAY;
            if (is_array($value) && ($value == array())) {
                return false;
            }
        }

        // ZERO_STRING ('0')
        if ($type >= self::TYPE_ZERO_STRING) {
            $type -= self::TYPE_ZERO_STRING;
            if (is_string($value) && ($value == '0')) {
                return false;
            }

            if (!$casting && (is_string($value)) && ($value == '1')) {
                return true;
            }
        }

        // STRING ('')
        if ($type >= self::TYPE_STRING) {
            $type -= self::TYPE_STRING;
            if (is_string($value) && ($value == '')) {
                return false;
            }
        }

        // FLOAT (0.0)
        if ($type >= self::TYPE_FLOAT) {
            $type -= self::TYPE_FLOAT;
            if (is_float($value) && ($value == 0.0)) {
                return false;
            }

            if (!$casting && is_float($value) && ($value == 1.0)) {
                return true;
            }
        }

        // INTEGER (0)
        if ($type >= self::TYPE_INTEGER) {
            $type -= self::TYPE_INTEGER;
            if (is_int($value) && ($value == 0)) {
                return false;
            }

            if (!$casting && is_int($value) && ($value == 1)) {
                return true;
            }
        }

        // BOOLEAN (false)
        if ($type >= self::TYPE_BOOLEAN) {
            $type -= self::TYPE_BOOLEAN;
            if (is_bool($value)) {
                return $value;
            }
        }

        if ($casting) {
            return true;
        }

        return $value;
    }
}
