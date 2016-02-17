<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\InputFilter;

/**
 * Implementors of this interface may report on the existence of unknown input,
 * as well as retrieve all unknown values.
 */
interface UnknownInputsCapableInterface
{
    /**
     * Is the data set has unknown input ?
     *
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function hasUnknown();

    /**
     * Return the unknown input
     *
     * @throws Exception\RuntimeException
     * @return array
     */
    public function getUnknown();
}
