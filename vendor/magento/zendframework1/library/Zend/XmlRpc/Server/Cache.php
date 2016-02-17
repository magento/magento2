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
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Server_Cache */
#require_once 'Zend/Server/Cache.php';

/**
 * Zend_XmlRpc_Server_Cache: cache Zend_XmlRpc_Server server definition
 *
 * @category   Zend
 * @package    Zend_XmlRpc
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_XmlRpc_Server_Cache extends Zend_Server_Cache
{
    /**
     * @var array Skip system methods when caching XML-RPC server
     */
    protected static $_skipMethods = array(
        'system.listMethods',
        'system.methodHelp',
        'system.methodSignature',
        'system.multicall',
    );
}
