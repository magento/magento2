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

class Blacklist extends AbstractFilter
{
    /**
     * @var bool
     */
    protected $strict = false;

    /**
     * @var array
     */
    protected $list = array();

    /**
     * @param null|array|Traversable $options
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Determine whether the in_array() call should be "strict" or not. See in_array docs.
     *
     * @param  bool $strict
     */
    public function setStrict($strict = true)
    {
        $this->strict = (bool) $strict;
    }

    /**
     * Returns whether the in_array() call should be "strict" or not. See in_array docs.
     *
     * @return boolean
     */
    public function getStrict()
    {
        return $this->strict;
    }

    /**
     * Set the list of items to black-list.
     *
     * @param  array|Traversable $list
     */
    public function setList($list = array())
    {
        if (!is_array($list)) {
            $list = ArrayUtils::iteratorToArray($list);
        }

        $this->list = $list;
    }

    /**
     * Get the list of items to black-list
     *
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * {@inheritDoc}
     *
     * Will return null if $value is present in the black-list. If $value is NOT present then it will return $value.
     */
    public function filter($value)
    {
        return in_array($value, $this->getList(), $this->getStrict()) ? null : $value;
    }
}
