<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter;

final class UpperCaseWords extends AbstractUnicode
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'encoding' => null
    );

    /**
     * Constructor
     *
     * @param string|array|\Traversable $encodingOrOptions OPTIONAL
     */
    public function __construct($encodingOrOptions = null)
    {
        if ($encodingOrOptions !== null) {
            if (static::isOptions($encodingOrOptions)) {
                $this->setOptions($encodingOrOptions);
            } else {
                $this->setEncoding($encodingOrOptions);
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * Returns the string $value, converting words to have an uppercase first character as necessary
     *
     * If the value provided is not a string, the value will remain unfiltered
     *
     * @param  string|mixed $value
     * @return string|mixed
     */
    public function filter($value)
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = (string) $value;

        if ($this->options['encoding'] !== null) {
            return mb_convert_case($value, MB_CASE_TITLE, $this->options['encoding']);
        }

        return ucwords(strtolower($value));
    }
}
