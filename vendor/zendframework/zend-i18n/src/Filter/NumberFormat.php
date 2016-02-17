<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\Filter;

use Zend\Stdlib\ErrorHandler;

class NumberFormat extends NumberParse
{
    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * @see    Zend\Filter\FilterInterface::filter()
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        if (!is_scalar($value)) {
            return $value;
        }

        if (!is_int($value) && !is_float($value)) {
            $result = parent::filter($value);
        } else {
            ErrorHandler::start();

            $result = $this->getFormatter()->format($value, $this->getType());

            ErrorHandler::stop();
        }

        if (false !== $result) {
            return $result;
        }

        return $value;
    }
}
