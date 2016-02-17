<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper\Escaper;

use Zend\Escaper;
use Zend\View\Exception;
use Zend\View\Helper;

/**
 * Helper for escaping values
 */
abstract class AbstractHelper extends Helper\AbstractHelper
{
    /**
     * @const Recursion constants
     */
    const RECURSE_NONE   = 0x00;
    const RECURSE_ARRAY  = 0x01;
    const RECURSE_OBJECT = 0x02;

    /**
     * @var string Encoding
     */
    protected $encoding = 'UTF-8';

    /**
     * @var Escaper\Escaper
     */
    protected $escaper = null;

    /**
     * Invoke this helper: escape a value
     *
     * @param  mixed $value
     * @param  int   $recurse Expects one of the recursion constants;
     *                        used to decide whether or not to recurse the given value when escaping
     * @throws Exception\InvalidArgumentException
     * @return mixed Given a scalar, a scalar value is returned. Given an object, with the $recurse flag not
     *               allowing object recursion, returns a string. Otherwise, returns an array.
     */
    public function __invoke($value, $recurse = self::RECURSE_NONE)
    {
        if (is_string($value)) {
            return $this->escape($value);
        }

        if (is_array($value)) {
            if (!(self::RECURSE_ARRAY & $recurse)) {
                throw new Exception\InvalidArgumentException(
                    'Array provided to Escape helper, but flags do not allow recursion'
                );
            }
            foreach ($value as $k => $v) {
                $value[$k] = $this->__invoke($v, $recurse);
            }
            return $value;
        }

        if (is_object($value)) {
            if (!(self::RECURSE_OBJECT & $recurse)) {
                // Attempt to cast it to a string
                if (method_exists($value, '__toString')) {
                    return $this->escape((string) $value);
                }
                throw new Exception\InvalidArgumentException(
                    'Object provided to Escape helper, but flags do not allow recursion'
                );
            }
            if (method_exists($value, 'toArray')) {
                return $this->__invoke($value->toArray(), $recurse | self::RECURSE_ARRAY);
            }
            return $this->__invoke((array) $value, $recurse | self::RECURSE_ARRAY);
        }

        return $value;
    }

    /**
     * Escape a value for current escaping strategy
     *
     * @param  string $value
     * @return string
     */
    abstract protected function escape($value);

    /**
     * Set the encoding to use for escape operations
     *
     * @param  string $encoding
     * @throws Exception\InvalidArgumentException
     * @return AbstractHelper
     */
    public function setEncoding($encoding)
    {
        if (null !== $this->escaper) {
            throw new Exception\InvalidArgumentException(
                'Character encoding settings cannot be changed once the Helper has been used or '
                . ' if a Zend\Escaper\Escaper object (with preset encoding option) is set.'
            );
        }

        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Get the encoding to use for escape operations
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set instance of Escaper
     *
     * @param  Escaper\Escaper $escaper
     * @return AbstractHelper
     */
    public function setEscaper(Escaper\Escaper $escaper)
    {
        $this->escaper  = $escaper;
        $this->encoding = $escaper->getEncoding();

        return $this;
    }

    /**
     * Get instance of Escaper
     *
     * @return null|Escaper\Escaper
     */
    public function getEscaper()
    {
        if (null === $this->escaper) {
            $this->setEscaper(new Escaper\Escaper($this->getEncoding()));
        }

        return $this->escaper;
    }
}
