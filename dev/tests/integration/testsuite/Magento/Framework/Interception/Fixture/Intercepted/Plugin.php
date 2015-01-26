<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
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
}
