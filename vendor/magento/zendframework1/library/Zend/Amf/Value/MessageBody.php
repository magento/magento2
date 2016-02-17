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
 * @package    Zend_Amf
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * An AMF Message contains information about the actual individual
 * transaction that is to be performed. It specifies the remote
 * operation that is to be performed; a local (client) operation
 * to be invoked upon success; and, the data to be used in the
 * operation.
 * <p/>
 * This Message structure defines how a local client would
 * invoke a method/operation on a remote server. Additionally,
 * the response from the Server is structured identically.
 *
 * @package    Zend_Amf
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Value_MessageBody
{
    /**
     * A string describing which operation, function, or method
     * is to be remotley invoked.
     * @var string
     */
    protected $_targetUri = "";

    /**
     * Universal Resource Identifier that uniquely targets the originator's
     * Object that should receive the server's response. The server will
     * use this path specification to target the "OnResult()" or "onStatus()"
     * handlers within the client. For Flash, it specifies an ActionScript
     * Object path only. The NetResponse object pointed to by the Response Uri
     * contains the connection state information. Passing/specifying this
     * provides a convenient mechanism for the client/server to share access
     * to an object that is managing the state of the shared connection.
     *
     * Since the server will use this field in the event of an error,
     * this field is required even if a successful server request would
     * not be expected to return a value to the client.
     *
     * @var string
     */
    protected $_responseUri = "";

    /**
     * Contains the actual data associated with the operation. It contains
     * the client's parameter data that is passed to the server's operation/method.
     * When serializing a root level data type or a parameter list array, no
     * name field is included. That is, the data is anonomously represented
     * as "Type Marker"/"Value" pairs. When serializing member data, the data is
     * represented as a series of "Name"/"Type"/"Value" combinations.
     *
     * For server generated responses, it may contain any ActionScript
     * data/objects that the server was expected to provide.
     *
     * @var string
     */
    protected $_data;

    /**
     * Constructor
     *
     * @param  string $targetUri
     * @param  string $responseUri
     * @param  string $data
     * @return void
     */
    public function __construct($targetUri, $responseUri, $data)
    {
        $this->setTargetUri($targetUri);
        $this->setResponseUri($responseUri);
        $this->setData($data);
    }

    /**
     * Retrieve target Uri
     *
     * @return string
     */
    public function getTargetUri()
    {
        return $this->_targetUri;
    }

    /**
     * Set target Uri
     *
     * @param  string $targetUri
     * @return Zend_Amf_Value_MessageBody
     */
    public function setTargetUri($targetUri)
    {
        if (null === $targetUri) {
            $targetUri = '';
        }
        $this->_targetUri = (string) $targetUri;
        return $this;
    }

    /**
     * Get target Uri
     *
     * @return string
     */
    public function getResponseUri()
    {
        return $this->_responseUri;
    }

    /**
     * Set response Uri
     *
     * @param  string $responseUri
     * @return Zend_Amf_Value_MessageBody
     */
    public function setResponseUri($responseUri)
    {
        if (null === $responseUri) {
            $responseUri = '';
        }
        $this->_responseUri = $responseUri;
        return $this;
    }

    /**
     * Retrieve response data
     *
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Set response data
     *
     * @param  mixed $data
     * @return Zend_Amf_Value_MessageBody
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * Set reply method
     *
     * @param  string $methodName
     * @return Zend_Amf_Value_MessageBody
     */
    public function setReplyMethod($methodName)
    {
        if (!preg_match('#^[/?]#', $methodName)) {
            $this->_targetUri = rtrim($this->_targetUri, '/') . '/';
        }
        $this->_targetUri = $this->_targetUri . $methodName;
        return $this;
    }
}
