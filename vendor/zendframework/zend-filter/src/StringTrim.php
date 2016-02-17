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

class StringTrim extends AbstractFilter
{
    /**
     * @var array
     */
    protected $options = array(
        'charlist' => null,
    );

    /**
     * Sets filter options
     *
     * @param  string|array|Traversable $charlistOrOptions
     */
    public function __construct($charlistOrOptions = null)
    {
        if ($charlistOrOptions !== null) {
            if (!is_array($charlistOrOptions) && !$charlistOrOptions  instanceof Traversable) {
                $this->setCharList($charlistOrOptions);
            } else {
                $this->setOptions($charlistOrOptions);
            }
        }
    }

    /**
     * Sets the charList option
     *
     * @param  string $charList
     * @return self Provides a fluent interface
     */
    public function setCharList($charList)
    {
        if (! strlen($charList)) {
            $charList = null;
        }

        $this->options['charlist'] = $charList;

        return $this;
    }

    /**
     * Returns the charList option
     *
     * @return string|null
     */
    public function getCharList()
    {
        return $this->options['charlist'];
    }

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Returns the string $value with characters stripped from the beginning and end
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        $value = (string) $value;

        if (null === $this->options['charlist']) {
            return $this->unicodeTrim($value);
        }

        return $this->unicodeTrim($value, $this->options['charlist']);
    }

    /**
     * Unicode aware trim method
     * Fixes a PHP problem
     *
     * @param string $value
     * @param string $charlist
     * @return string
     */
    protected function unicodeTrim($value, $charlist = '\\\\s')
    {
        $chars = preg_replace(
            array('/[\^\-\]\\\]/S', '/\\\{4}/S', '/\//'),
            array('\\\\\\0', '\\', '\/'),
            $charlist
        );

        $pattern = '/^[' . $chars . ']+|[' . $chars . ']+$/usSD';

        return preg_replace($pattern, '', $value);
    }
}
