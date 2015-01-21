<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Interception\Fixture\Intercepted;

use Magento\Framework\Interception\Fixture\Intercepted;

class FirstPlugin
{
    /**
     * @var int
     */
    protected $_counter = 0;

    public function aroundC(Intercepted $subject, \Closure $next, $param1)
    {
        return '<F:C>' . $next($param1) . '</F:C>';
    }

    public function aroundD(Intercepted $subject, \Closure $next, $param1)
    {
        $this->_counter++;
        return '<F:D>' . $this->_counter . ': ' . $next($param1) . '</F:D>';
    }

    public function aroundK(Intercepted $subject, \Closure $next, $param1)
    {
        $result = $subject->C($param1);
        return '<F:K>' . $subject->F($result) . '</F:K>';
    }

    public function beforeG(Intercepted $subject, $param1)
    {
        return ['<F:bG>' . $param1 . '</F:bG>'];
    }

    public function aroundG(Intercepted $subject, \Closure $next, $param1)
    {
        return $next('<F:G>' . $param1 . '</F:G>');
    }

    public function afterG(Intercepted $subject, $result)
    {
        return '<F:aG>' . $result . '</F:aG>';
    }

    public function beforeV(Intercepted $subject, $param1)
    {
        return ['<F:bV/>'];
    }

    public function aroundV(Intercepted $subject, \Closure $next, $param1)
    {
        return '<F:V>' . $param1 . '<F:V/>';
    }

    public function beforeW(Intercepted $subject, $param1)
    {
        return ['<F:bW/>'];
    }

    public function aroundW(Intercepted $subject, \Closure $next, $param1)
    {
        return '<F:W>' . $param1 . '<F:W/>';
    }

    public function afterW(Intercepted $subject, $result)
    {
        return '<F:aW/>';
    }

    public function beforeX(Intercepted $subject, $param1)
    {
        return ['<F:bX/>'];
    }

    public function aroundY(Intercepted $subject, \Closure $next, $param1)
    {
        return '<F:Y>' . $param1 . '<F:Y/>';
    }

    public function afterZ(Intercepted $subject, $result)
    {
        return '<F:aZ/>';
    }
}
