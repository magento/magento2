<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_XmlRpc
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Scalar.php 22024 2010-04-27 18:08:24Z matthew $
 */


/**
 * Zend_XmlRpc_Value
 */
#require_once 'Zend/XmlRpc/Value.php';


/**
 * @category   Zend
 * @package    Zend_XmlRpc
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_XmlRpc_Value_Scalar extends Zend_XmlRpc_Value
{
    /**
     * Generate the XML code that represent a scalar native MXL-RPC value
     *
     * @return void
     */
    protected function _generateXml()
    {
        $generator = $this->getGenerator();

        $generator->openElement('value')
                  ->openElement($this->_type, $this->_value)
                  ->closeElement($this->_type)
                  ->closeElement('value');
    }
}