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
namespace Magento\Framework\Interception\Fixture;

class Intercepted extends InterceptedParent implements InterceptedInterface
{
    protected $_key;

    public function A($param1)
    {
        $this->_key = $param1;
        return $this;
    }

    public function B($param1, $param2)
    {
        return '<B>' . $param1 . $param2 . $this->C($param1) . '</B>';
    }

    public function C($param1)
    {
        return '<C>' . $param1 . '</C>';
    }

    public function D($param1)
    {
        return '<D>' . $this->_key . $param1 . '</D>';
    }

    final public function E($param1)
    {
        return '<E>' . $this->_key . $param1 . '</E>';
    }

    public function F($param1)
    {
        return '<F>' . $param1 . '</F>';
    }

    public function G($param1)
    {
        return '<G>' . $param1 . "</G>";
    }

    public function K($param1)
    {
        return '<K>' . $param1 . '</K>';
    }
}
