<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper\Placeholder\Container;

use ArrayObject;
use Zend\View\Exception;

/**
 * Abstract class representing container for placeholder values
 */
abstract class AbstractContainer extends ArrayObject
{
    /**
     * Whether or not to override all contents of placeholder
     *
     * @const string
     */
    const SET = 'SET';

    /**
     * Whether or not to append contents to placeholder
     *
     * @const string
     */
    const APPEND = 'APPEND';

    /**
     * Whether or not to prepend contents to placeholder
     *
     * @const string
     */
    const PREPEND = 'PREPEND';

    /**
     * Key to which to capture content
     *
     * @var string
     */
    protected $captureKey;

    /**
     * Whether or not we're already capturing for this given container
     *
     * @var bool
     */
    protected $captureLock = false;

    /**
     * What type of capture (overwrite (set), append, prepend) to use
     *
     * @var string
     */
    protected $captureType;

    /**
     * What string to use as the indentation of output, this will typically be spaces. Eg: '    '
     *
     * @var string
     */
    protected $indent = '';

    /**
     * What text to append the placeholder with when rendering
     *
     * @var string
     */
    protected $postfix   = '';

    /**
     * What text to prefix the placeholder with when rendering
     *
     * @var string
     */
    protected $prefix    = '';

    /**
     * What string to use between individual items in the placeholder when rendering
     *
     * @var string
     */
    protected $separator = '';

    /**
     * Constructor - This is needed so that we can attach a class member as the ArrayObject container
     */
    public function __construct()
    {
        parent::__construct(array(), parent::ARRAY_AS_PROPS);
    }

    /**
     * Serialize object to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Render the placeholder
     *
     * @param  null|int|string $indent
     * @return string
     */
    public function toString($indent = null)
    {
        $indent = ($indent !== null)
            ? $this->getWhitespace($indent)
            : $this->getIndent();

        $items  = $this->getArrayCopy();
        $return = $indent
            . $this->getPrefix()
            . implode($this->getSeparator(), $items)
            . $this->getPostfix();
        $return = preg_replace("/(\r\n?|\n)/", '$1' . $indent, $return);

        return $return;
    }

    /**
     * Start capturing content to push into placeholder
     *
     * @param  string $type How to capture content into placeholder; append, prepend, or set
     * @param  mixed  $key  Key to which to capture content
     * @throws Exception\RuntimeException if nested captures detected
     * @return void
     */
    public function captureStart($type = AbstractContainer::APPEND, $key = null)
    {
        if ($this->captureLock) {
            throw new Exception\RuntimeException(
                'Cannot nest placeholder captures for the same placeholder'
            );
        }

        $this->captureLock = true;
        $this->captureType = $type;
        if ((null !== $key) && is_scalar($key)) {
            $this->captureKey = (string) $key;
        }
        ob_start();
    }

    /**
     * End content capture
     *
     * @return void
     */
    public function captureEnd()
    {
        $data               = ob_get_clean();
        $key                = null;
        $this->captureLock = false;
        if (null !== $this->captureKey) {
            $key = $this->captureKey;
        }
        switch ($this->captureType) {
            case self::SET:
                if (null !== $key) {
                    $this[$key] = $data;
                } else {
                    $this->exchangeArray(array($data));
                }
                break;
            case self::PREPEND:
                if (null !== $key) {
                    $array  = array($key => $data);
                    $values = $this->getArrayCopy();
                    $final  = $array + $values;
                    $this->exchangeArray($final);
                } else {
                    $this->prepend($data);
                }
                break;
            case self::APPEND:
            default:
                if (null !== $key) {
                    if (empty($this[$key])) {
                        $this[$key] = $data;
                    } else {
                        $this[$key] .= $data;
                    }
                } else {
                    $this[$this->nextIndex()] = $data;
                }
                break;
        }
    }

    /**
     * Get keys
     *
     * @return array
     */
    public function getKeys()
    {
        $array = $this->getArrayCopy();

        return array_keys($array);
    }

    /**
     * Retrieve container value
     *
     * If single element registered, returns that element; otherwise,
     * serializes to array.
     *
     * @return mixed
     */
    public function getValue()
    {
        if (1 == count($this)) {
            $keys = $this->getKeys();
            $key  = array_shift($keys);
            return $this[$key];
        }

        return $this->getArrayCopy();
    }

    /**
     * Retrieve whitespace representation of $indent
     *
     * @param  int|string $indent
     * @return string
     */
    public function getWhitespace($indent)
    {
        if (is_int($indent)) {
            $indent = str_repeat(' ', $indent);
        }

        return (string) $indent;
    }

    /**
     * Set a single value
     *
     * @param  mixed $value
     * @return void
     */
    public function set($value)
    {
        $this->exchangeArray(array($value));

        return $this;
    }

    /**
     * Prepend a value to the top of the container
     *
     * @param  mixed $value
     * @return self
     */
    public function prepend($value)
    {
        $values = $this->getArrayCopy();
        array_unshift($values, $value);
        $this->exchangeArray($values);

        return $this;
    }

    /**
     * Append a value to the end of the container
     *
     * @param  mixed $value
     * @return self
     */
    public function append($value)
    {
        parent::append($value);
        return $this;
    }

    /**
     * Next Index as defined by the PHP manual
     *
     * @return int
     */
    public function nextIndex()
    {
        $keys = $this->getKeys();
        if (0 == count($keys)) {
            return 0;
        }

        return max($keys) + 1;
    }

    /**
     * Set the indentation string for __toString() serialization,
     * optionally, if a number is passed, it will be the number of spaces
     *
     * @param  string|int $indent
     * @return self
     */
    public function setIndent($indent)
    {
        $this->indent = $this->getWhitespace($indent);
        return $this;
    }

    /**
     * Retrieve indentation
     *
     * @return string
     */
    public function getIndent()
    {
        return $this->indent;
    }

    /**
     * Set postfix for __toString() serialization
     *
     * @param  string $postfix
     * @return self
     */
    public function setPostfix($postfix)
    {
        $this->postfix = (string) $postfix;
        return $this;
    }

    /**
     * Retrieve postfix
     *
     * @return string
     */
    public function getPostfix()
    {
        return $this->postfix;
    }

    /**
     * Set prefix for __toString() serialization
     *
     * @param  string $prefix
     * @return self
     */
    public function setPrefix($prefix)
    {
        $this->prefix = (string) $prefix;
        return $this;
    }

    /**
     * Retrieve prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set separator for __toString() serialization
     *
     * Used to implode elements in container
     *
     * @param  string $separator
     * @return self
     */
    public function setSeparator($separator)
    {
        $this->separator = (string) $separator;
        return $this;
    }

    /**
     * Retrieve separator
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }
}
