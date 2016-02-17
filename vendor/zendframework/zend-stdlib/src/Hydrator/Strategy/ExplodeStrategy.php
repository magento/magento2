<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\Hydrator\Strategy;

final class ExplodeStrategy implements StrategyInterface
{
    /**
     * @var string
     */
    private $valueDelimiter;

    /**
     * @var int|null
     */
    private $explodeLimit;

    /**
     * Constructor
     *
     * @param string   $delimiter    String that the values will be split upon
     * @param int|null $explodeLimit Explode limit
     */
    public function __construct($delimiter = ',', $explodeLimit = null)
    {
        $this->setValueDelimiter($delimiter);

        $this->explodeLimit = ($explodeLimit === null) ? null : (int) $explodeLimit;
    }

    /**
     * Sets the delimiter string that the values will be split upon
     *
     * @param  string $delimiter
     * @return self
     */
    private function setValueDelimiter($delimiter)
    {
        if (!is_string($delimiter)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects Delimiter to be string, %s provided instead',
                __METHOD__,
                is_object($delimiter) ? get_class($delimiter) : gettype($delimiter)
            ));
        }

        if (empty($delimiter)) {
            throw new Exception\InvalidArgumentException('Delimiter cannot be empty.');
        }

        $this->valueDelimiter = $delimiter;
    }

    /**
     * {@inheritDoc}
     *
     * Split a string by delimiter
     *
     * @param string|null $value
     *
     * @return string[]
     *
     * @throws Exception\InvalidArgumentException
     */
    public function hydrate($value)
    {
        if (null === $value) {
            return array();
        }

        if (!(is_string($value) || is_numeric($value))) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects argument 1 to be string, %s provided instead',
                __METHOD__,
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        if ($this->explodeLimit !== null) {
            return explode($this->valueDelimiter, $value, $this->explodeLimit);
        }

        return explode($this->valueDelimiter, $value);
    }

    /**
     * {@inheritDoc}
     *
     * Join array elements with delimiter
     *
     * @param string[] $value The original value.
     *
     * @return string|null
     */
    public function extract($value)
    {
        if (!is_array($value)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects argument 1 to be array, %s provided instead',
                __METHOD__,
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        return empty($value) ? null : implode($this->valueDelimiter, $value);
    }
}
