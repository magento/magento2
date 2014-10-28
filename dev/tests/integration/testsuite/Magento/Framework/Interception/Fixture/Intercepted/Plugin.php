<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Interception\Fixture\Intercepted;

use Magento\Framework\Interception\Fixture\Intercepted;

class Plugin
{
    /**
     * @var int
     */
    protected $_counter = 0;

    public function aroundC(Intercepted $subject, \Closure $next, $param1)
    {
        return '<P:C>' . $next($param1) . '</P:C>';
    }

    public function aroundD(Intercepted $subject, \Closure $next, $param1)
    {
        $this->_counter++;
        return '<P:D>' . $this->_counter . ': ' . $next($param1) . '</P:D>';
    }

    public function aroundK(Intercepted $subject, \Closure $next, $param1)
    {
        $result = $subject->C($param1);
        return '<P:K>' . $subject->F($result) . '</P:K>';
    }

    public function beforeG(Intercepted $subject, $param1)
    {
        return array('<P:bG>' . $param1 . '</P:bG>');
    }

    public function aroundG(Intercepted $subject, \Closure $next, $param1)
    {
        return $next('<P:G>' . $param1 . '</P:G>');
    }

    public function afterG(Intercepted $subject, $result)
    {
        return '<P:aG>' . $result . '</P:aG>';
    }
}
