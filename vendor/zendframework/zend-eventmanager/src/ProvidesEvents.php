<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

trigger_error('Zend\EventManager\ProvidesEvents has been deprecated in favor of Zend\EventManager\EventManagerAwareTrait; please update your code', E_USER_DEPRECATED);

/**
 * @deprecated Please use EventManagerAwareTrait instead.
 *
 * This trait exists solely for backwards compatibility in the 2.x branch and
 * will likely be removed in 3.x.
 */
trait ProvidesEvents
{
    use EventManagerAwareTrait;
}
