<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Server;

/**
 * Client Interface
 */
interface Client
{
    /**
     * Executes remote call
     *
     * Unified interface for calling custom remote methods.
     *
     * @param  string $method Remote call name.
     * @param  array $params Call parameters.
     * @return mixed Remote call results.
     */
    public function call($method, $params = array());
}
