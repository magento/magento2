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
 * @package    Zend_Date
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Exception.php 20279 2010-01-14 15:21:47Z thomas $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * Zend_Exception
 */
#require_once 'Zend/Exception.php';


/**
 * @category   Zend
 * @package    Zend_Date
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Date_Exception extends Zend_Exception
{
    protected $operand = null;

    public function __construct($message, $code = 0, $e = null, $op = null)
    {
        $this->operand = $op;
        parent::__construct($message, $code, $e);
    }

    public function getOperand()
    {
        return $this->operand;
    }
}
