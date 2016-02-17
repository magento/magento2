<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

use Traversable;
use Zend\Stdlib\ArrayUtils;

class NotEmpty extends AbstractValidator
{
    const BOOLEAN       = 0x001;
    const INTEGER       = 0x002;
    const FLOAT         = 0x004;
    const STRING        = 0x008;
    const ZERO          = 0x010;
    const EMPTY_ARRAY   = 0x020;
    const NULL          = 0x040;
    const PHP           = 0x07F;
    const SPACE         = 0x080;
    const OBJECT        = 0x100;
    const OBJECT_STRING = 0x200;
    const OBJECT_COUNT  = 0x400;
    const ALL           = 0x7FF;

    const INVALID  = 'notEmptyInvalid';
    const IS_EMPTY = 'isEmpty';

    protected $constants = array(
        self::BOOLEAN       => 'boolean',
        self::INTEGER       => 'integer',
        self::FLOAT         => 'float',
        self::STRING        => 'string',
        self::ZERO          => 'zero',
        self::EMPTY_ARRAY   => 'array',
        self::NULL          => 'null',
        self::PHP           => 'php',
        self::SPACE         => 'space',
        self::OBJECT        => 'object',
        self::OBJECT_STRING => 'objectstring',
        self::OBJECT_COUNT  => 'objectcount',
        self::ALL           => 'all',
    );

    /**
     * Default value for types; value = 0b000111101001
     *
     * @var array
     */
    protected $defaultType = array(
        self::OBJECT,
        self::SPACE,
        self::NULL,
        self::EMPTY_ARRAY,
        self::STRING,
        self::BOOLEAN
    );

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::IS_EMPTY => "Value is required and can't be empty",
        self::INVALID  => "Invalid type given. String, integer, float, boolean or array expected",
    );

    /**
     * Options for this validator
     *
     * @var array
     */
    protected $options = array();

    /**
     * Constructor
     *
     * @param  array|Traversable|int $options OPTIONAL
     */
    public function __construct($options = null)
    {
        $this->setType($this->defaultType);

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            $options = func_get_args();
            $temp    = array();
            if (!empty($options)) {
                $temp['type'] = array_shift($options);
            }

            $options = $temp;
        }

        if (is_array($options)) {
            if (!array_key_exists('type', $options)) {
                $detected = 0;
                $found    = false;
                foreach ($options as $option) {
                    if (in_array($option, $this->constants, true)) {
                        $found = true;
                        $detected += array_search($option, $this->constants);
                    }
                }

                if ($found) {
                    $options['type'] = $detected;
                }
            }
        }

        parent::__construct($options);
    }

    /**
     * Returns the set types
     *
     * @return array
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * @return int
     */
    public function getDefaultType()
    {
        return $this->calculateTypeValue($this->defaultType);
    }

    /**
     * @param array|int|string $type
     * @return int
     */
    protected function calculateTypeValue($type)
    {
        if (is_array($type)) {
            $detected = 0;
            foreach ($type as $value) {
                if (is_int($value)) {
                    $detected |= $value;
                } elseif (in_array($value, $this->constants)) {
                    $detected |= array_search($value, $this->constants);
                }
            }

            $type = $detected;
        } elseif (is_string($type) && in_array($type, $this->constants)) {
            $type = array_search($type, $this->constants);
        }

        return $type;
    }

    /**
     * Set the types
     *
     * @param  int|array $type
     * @throws Exception\InvalidArgumentException
     * @return NotEmpty
     */
    public function setType($type = null)
    {
        $type = $this->calculateTypeValue($type);

        if (!is_int($type) || ($type < 0) || ($type > self::ALL)) {
            throw new Exception\InvalidArgumentException('Unknown type');
        }

        $this->options['type'] = $type;

        return $this;
    }

    /**
     * Returns true if and only if $value is not an empty value.
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        if ($value !== null && !is_string($value) && !is_int($value) && !is_float($value) &&
            !is_bool($value) && !is_array($value) && !is_object($value)
        ) {
            $this->error(self::INVALID);
            return false;
        }

        $type    = $this->getType();
        $this->setValue($value);
        $object  = false;

        // OBJECT_COUNT (countable object)
        if ($type & self::OBJECT_COUNT) {
            $object = true;

            if (is_object($value) && ($value instanceof \Countable) && (count($value) == 0)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // OBJECT_STRING (object's toString)
        if ($type & self::OBJECT_STRING) {
            $object = true;

            if ((is_object($value) && (!method_exists($value, '__toString'))) ||
                (is_object($value) && (method_exists($value, '__toString')) && (((string) $value) == ""))) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // OBJECT (object)
        if ($type & self::OBJECT) {
            // fall trough, objects are always not empty
        } elseif ($object === false) {
            // object not allowed but object given -> return false
            if (is_object($value)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // SPACE ('   ')
        if ($type & self::SPACE) {
            if (is_string($value) && (preg_match('/^\s+$/s', $value))) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // NULL (null)
        if ($type & self::NULL) {
            if ($value === null) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // EMPTY_ARRAY (array())
        if ($type & self::EMPTY_ARRAY) {
            if (is_array($value) && ($value == array())) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // ZERO ('0')
        if ($type & self::ZERO) {
            if (is_string($value) && ($value == '0')) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // STRING ('')
        if ($type & self::STRING) {
            if (is_string($value) && ($value == '')) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // FLOAT (0.0)
        if ($type & self::FLOAT) {
            if (is_float($value) && ($value == 0.0)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // INTEGER (0)
        if ($type & self::INTEGER) {
            if (is_int($value) && ($value == 0)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        // BOOLEAN (false)
        if ($type & self::BOOLEAN) {
            if (is_bool($value) && ($value == false)) {
                $this->error(self::IS_EMPTY);
                return false;
            }
        }

        return true;
    }
}
