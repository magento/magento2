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

/**
 * XML-RPC system.* methods
 *
 * @category   Zend
 * @package    Zend_XmlRpc
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_XmlRpc_Server_System
{
    /**
     * @var Zend_XmlRpc_Server
     */
    protected $_server;

    /**
     * Constructor
     *
     * @param  Zend_XmlRpc_Server $server
     * @return void
     */
    public function __construct(Zend_XmlRpc_Server $server)
    {
        $this->_server = $server;
    }

    /**
     * List all available XMLRPC methods
     *
     * Returns an array of methods.
     *
     * @return array
     */
    public function listMethods()
    {
        $table = $this->_server->getDispatchTable()->getMethods();
        return array_keys($table);
    }

    /**
     * Display help message for an XMLRPC method
     *
     * @param string $method
     * @return string
     */
    public function methodHelp($method)
    {
        $table = $this->_server->getDispatchTable();
        if (!$table->hasMethod($method)) {
            #require_once 'Zend/XmlRpc/Server/Exception.php';
            throw new Zend_XmlRpc_Server_Exception('Method "' . $method . '" does not exist', 640);
        }

        return $table->getMethod($method)->getMethodHelp();
    }

    /**
     * Return a method signature
     *
     * @param string $method
     * @return array
     */
    public function methodSignature($method)
    {
        $table = $this->_server->getDispatchTable();
        if (!$table->hasMethod($method)) {
            #require_once 'Zend/XmlRpc/Server/Exception.php';
            throw new Zend_XmlRpc_Server_Exception('Method "' . $method . '" does not exist', 640);
        }
        $method = $table->getMethod($method)->toArray();
        return $method['prototypes'];
    }

    /**
     * Multicall - boxcar feature of XML-RPC for calling multiple methods
     * in a single request.
     *
     * Expects a an array of structs representing method calls, each element
     * having the keys:
     * - methodName
     * - params
     *
     * Returns an array of responses, one for each method called, with the value
     * returned by the method. If an error occurs for a given method, returns a
     * struct with a fault response.
     *
     * @see http://www.xmlrpc.com/discuss/msgReader$1208
     * @param  array $methods
     * @return array
     */
    public function multicall($methods)
    {
        $responses = array();
        foreach ($methods as $method) {
            $fault = false;
            if (!is_array($method)) {
                $fault = $this->_server->fault('system.multicall expects each method to be a struct', 601);
            } elseif (!isset($method['methodName'])) {
                $fault = $this->_server->fault('Missing methodName: ' . var_export($methods, 1), 602);
            } elseif (!isset($method['params'])) {
                $fault = $this->_server->fault('Missing params', 603);
            } elseif (!is_array($method['params'])) {
                $fault = $this->_server->fault('Params must be an array', 604);
            } else {
                if ('system.multicall' == $method['methodName']) {
                    // don't allow recursive calls to multicall
                    $fault = $this->_server->fault('Recursive system.multicall forbidden', 605);
                }
            }

            if (!$fault) {
                try {
                    $request = new Zend_XmlRpc_Request();
                    $request->setMethod($method['methodName']);
                    $request->setParams($method['params']);
                    $response = $this->_server->handle($request);
                    if ($response instanceof Zend_XmlRpc_Fault
                        || $response->isFault()
                    ) {
                        $fault = $response;
                    } else {
                        $responses[] = $response->getReturnValue();
                    }
                } catch (Exception $e) {
                    $fault = $this->_server->fault($e);
                }
            }

            if ($fault) {
                $responses[] = array(
                    'faultCode'   => $fault->getCode(),
                    'faultString' => $fault->getMessage()
                );
            }
        }

        return $responses;
    }
}
