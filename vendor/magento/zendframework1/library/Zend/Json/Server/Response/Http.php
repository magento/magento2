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
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Json_Server_Response
 */
#require_once 'Zend/Json/Server/Response.php';

/**
 * @category   Zend
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Json_Server_Response_Http extends Zend_Json_Server_Response
{
    /**
     * Emit JSON
     *
     * Send appropriate HTTP headers. If no Id, then return an empty string.
     *
     * @return string
     */
    public function toJson()
    {
        $this->sendHeaders();
        if (!$this->isError() && null === $this->getId()) {
            return '';
        }

        return parent::toJson();
    }

    /**
     * Send headers
     *
     * If headers are already sent, do nothing. If null ID, send HTTP 204
     * header. Otherwise, send content type header based on content type of
     * service map.
     *
     * @return void
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            return;
        }

        if (!$this->isError() && (null === $this->getId())) {
            header('HTTP/1.1 204 No Content');
            return;
        }

        if (null === ($smd = $this->getServiceMap())) {
            return;
        }

        $contentType = $smd->getContentType();
        if (!empty($contentType)) {
            header('Content-Type: ' . $contentType);
        }
    }
}
