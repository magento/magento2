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

abstract class AbstractDateDropdown extends AbstractFilter
{
    /**
     * If true, the filter will return null if any date field is empty
     *
     * @var bool
     */
    protected $nullOnEmpty = false;

    /**
     * If true, the filter will return null if all date fields are empty
     *
     * @var bool
     */
    protected $nullOnAllEmpty = false;

    /**
     * Sprintf format string to use for formatting the date, fields will be used in alphabetical order.
     *
     * @var string
     */
    protected $format = '';

    /**
     * @var int
     */
    protected $expectedInputs;

    /**
     * @param mixed $options If array or Traversable, passes value to
     *     setOptions().
     */
    public function __construct($options = null)
    {
        if (is_array($options) || $options instanceof Traversable) {
            $this->setOptions($options);
        }
    }

    /**
     * @param bool $nullOnAllEmpty
     * @return self
     */
    public function setNullOnAllEmpty($nullOnAllEmpty)
    {
        $this->nullOnAllEmpty = $nullOnAllEmpty;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNullOnAllEmpty()
    {
        return $this->nullOnAllEmpty;
    }

    /**
     * @param bool $nullOnEmpty
     * @return self
     */
    public function setNullOnEmpty($nullOnEmpty)
    {
        $this->nullOnEmpty = $nullOnEmpty;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNullOnEmpty()
    {
        return $this->nullOnEmpty;
    }

    /**
     * Attempts to filter an array of date/time information to a formatted
     * string.
     *
     * @param  mixed $value
     * @return mixed
     * @throws Exception\RuntimeException If filtering $value is impossible
     */
    public function filter($value)
    {
        if (! is_array($value)) {
            // nothing to do
            return $value;
        }

        // Convert the date to a specific format
        if ($this->isNullOnEmpty()
            && array_reduce($value, __CLASS__ . '::reduce', false)
        ) {
            return;
        }

        if ($this->isNullOnAllEmpty()
            && array_reduce($value, __CLASS__ . '::reduce', true)
        ) {
            return;
        }

        $this->filterable($value);

        ksort($value);
        $value = vsprintf($this->format, $value);

        return $value;
    }

    /**
     * Ensures there are enough inputs in the array to properly format the date.
     *
     * @param $value
     * @throws Exception\RuntimeException
     */
    protected function filterable($value)
    {
        if (count($value) !== $this->expectedInputs) {
            throw new Exception\RuntimeException(
                sprintf(
                    'There are not enough values in the array to filter this date (Required: %d, Received: %d)',
                    $this->expectedInputs,
                    count($value)
                )
            );
        }
    }

    /**
     * Reduce to a single value
     *
     * @param string $soFar
     * @param string $value
     * @return bool
     */
    public static function reduce($soFar, $value)
    {
        return $soFar || empty($value);
    }
}
