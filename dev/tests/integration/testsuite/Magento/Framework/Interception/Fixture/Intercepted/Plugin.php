<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Fixture\Intercepted;

use Magento\Framework\Interception\Fixture\Intercepted;

class Plugin
{
    /**
     * @var int
     */
    protected $_counter = 0;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundC(Intercepted $subject, \Closure $next, $param1)
    {
        return '<P:C>' . $next($param1) . '</P:C>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundD(Intercepted $subject, \Closure $next, $param1)
    {
        $this->_counter++;
        return '<P:D>' . $this->_counter . ': ' . $next($param1) . '</P:D>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundK(Intercepted $subject, \Closure $next, $param1)
    {
        $result = $subject->C($param1);
        return '<P:K>' . $subject->F($result) . '</P:K>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeG(Intercepted $subject, $param1)
    {
        return ['<P:bG>' . $param1 . '</P:bG>'];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundG(Intercepted $subject, \Closure $next, $param1)
    {
        return $next('<P:G>' . $param1 . '</P:G>');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterG(Intercepted $subject, $result)
    {
        return '<P:aG>' . $result . '</P:aG>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeV(Intercepted $subject, $param1)
    {
        return ['<P:bV/>'];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundV(Intercepted $subject, \Closure $next, $param1)
    {
        return '<P:V>' . $param1 . '<P:V/>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeW(Intercepted $subject, $param1)
    {
        return ['<P:bW/>'];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundW(Intercepted $subject, \Closure $next, $param1)
    {
        return '<P:W>' . $param1 . '<P:W/>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterW(Intercepted $subject, $result)
    {
        return '<P:aW/>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeX(Intercepted $subject, $param1)
    {
        return ['<P:bX/>'];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundY(Intercepted $subject, \Closure $next, $param1)
    {
        return '<P:Y>' . $param1 . '<P:Y/>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterZ(Intercepted $subject, $result)
    {
        return '<P:aZ/>';
    }
}
