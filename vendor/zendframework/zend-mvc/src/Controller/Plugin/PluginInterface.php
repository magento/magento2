<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Controller\Plugin;

use Zend\Stdlib\DispatchableInterface as Dispatchable;

interface PluginInterface
{
    /**
     * Set the current controller instance
     *
     * @param  Dispatchable $controller
     * @return void
     */
    public function setController(Dispatchable $controller);

    /**
     * Get the current controller instance
     *
     * @return null|Dispatchable
     */
    public function getController();
}
