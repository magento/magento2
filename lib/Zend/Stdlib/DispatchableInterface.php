<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Stdlib
 */

namespace Zend\Stdlib;

use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;

/**
 * @category   Zend
 * @package    Zend_Stdlib
 */
interface DispatchableInterface
{
    /**
     * Dispatch a request
     *
     * @param Request $request
     * @param null|Response $response
     * @return Response|mixed
     */
    public function dispatch(Request $request, Response $response = null);
}
