<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter;

class DateSelect extends AbstractDateDropdown
{
    /**
     * Year-Month-Day
     *
     * @var string
     */
    protected $format = '%3$s-%2$s-%1$s';

    /**
     * @var int
     */
    protected $expectedInputs = 3;
}
