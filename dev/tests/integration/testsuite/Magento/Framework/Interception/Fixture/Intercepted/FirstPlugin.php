<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Fixture\Intercepted;

use Magento\Framework\Interception\Fixture\Intercepted;

class FirstPlugin
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
        return '<F:C>' . $next($param1) . '</F:C>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundD(Intercepted $subject, \Closure $next, $param1)
    {
        $this->_counter++;
        return '<F:D>' . $this->_counter . ': ' . $next($param1) . '</F:D>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundK(Intercepted $subject, \Closure $next, $param1)
    {
        $result = $subject->C($param1);
        return '<F:K>' . $subject->F($result) . '</F:K>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeG(Intercepted $subject, $param1)
    {
        return ['<F:bG>' . $param1 . '</F:bG>'];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundG(Intercepted $subject, \Closure $next, $param1)
    {
        return $next('<F:G>' . $param1 . '</F:G>');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterG(Intercepted $subject, $result)
    {
        return '<F:aG>' . $result . '</F:aG>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeV(Intercepted $subject, $param1)
    {
        return ['<F:bV/>'];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundV(Intercepted $subject, \Closure $next, $param1)
    {
        return '<F:V>' . $param1 . '<F:V/>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeW(Intercepted $subject, $param1)
    {
        return ['<F:bW/>'];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundW(Intercepted $subject, \Closure $next, $param1)
    {
        return '<F:W>' . $param1 . '<F:W/>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterW(Intercepted $subject, $result)
    {
        return '<F:aW/>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeX(Intercepted $subject, $param1)
    {
        return ['<F:bX/>'];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundY(Intercepted $subject, \Closure $next, $param1)
    {
        return '<F:Y>' . $param1 . '<F:Y/>';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterZ(Intercepted $subject, $result)
    {
        return '<F:aZ/>';
    }
}
