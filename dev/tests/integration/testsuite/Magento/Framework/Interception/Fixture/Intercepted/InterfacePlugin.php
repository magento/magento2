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

use Magento\Framework\Interception\Fixture\InterceptedInterface;

class InterfacePlugin
{
    /**
     * @param InterceptedInterface $subject
     * @param \Closure $next
     * @param string $param1
     * @return string
     */
    public function aroundC(InterceptedInterface $subject, \Closure $next, $param1)
    {
        return '<IP:C>' . $next($param1) . '</IP:C>';
    }

    /**
     * @param InterceptedInterface $subject
     * @param \Closure $next
     * @param $param1
     * @return string
     */
    public function aroundF(InterceptedInterface $subject, \Closure $next, $param1)
    {
        return '<IP:F>' . $subject->D($next($subject->C($param1))) . '</IP:F>';
    }

    public function beforeG(InterceptedInterface $subject, $param1)
    {
        return array('<IP:bG>' . $param1 . '</IP:bG>');
    }

    public function aroundG(InterceptedInterface $subject, \Closure $next, $param1)
    {
        return $next('<IP:G>' . $param1 . '</IP:G>');
    }

    public function afterG(InterceptedInterface $subject, $result)
    {
        return '<IP:aG>' . $result . '</IP:aG>';
    }
}
