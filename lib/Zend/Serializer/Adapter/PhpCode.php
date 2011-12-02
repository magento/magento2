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
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: PhpCode.php 20574 2010-01-24 17:39:14Z mabe $
 */

/** @see Zend_Serializer_Adapter_AdapterAbstract */
#require_once 'Zend/Serializer/Adapter/AdapterAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Serializer_Adapter_PhpCode extends Zend_Serializer_Adapter_AdapterAbstract
{
    /**
     * Serialize PHP using var_export
     * 
     * @param  mixed $value 
     * @param  array $opts 
     * @return string
     */
    public function serialize($value, array $opts = array())
    {
        return var_export($value, true);
    }

    /**
     * Deserialize PHP string
     *
     * Warning: this uses eval(), and should likely be avoided.
     * 
     * @param  string $code 
     * @param  array $opts 
     * @return mixed
     * @throws Zend_Serializer_Exception on eval error
     */
    public function unserialize($code, array $opts = array())
    {
        $eval = @eval('$ret=' . $code . ';');
        if ($eval === false) {
                $lastErr = error_get_last();
                #require_once 'Zend/Serializer/Exception.php';
                throw new Zend_Serializer_Exception('eval failed: ' . $lastErr['message']);
        }
        return $ret;
    }
}
