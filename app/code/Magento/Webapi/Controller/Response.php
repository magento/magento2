<?php
/**
 * Web API response.
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
namespace Magento\Webapi\Controller;

class Response extends \Zend_Controller_Response_Http implements \Magento\Framework\App\Response\HttpInterface
{
    /**
     * Character set which must be used in response.
     */
    const RESPONSE_CHARSET = 'utf-8';

    /**#@+
     * Default message types.
     */
    const MESSAGE_TYPE_SUCCESS = 'success';

    const MESSAGE_TYPE_ERROR = 'error';

    const MESSAGE_TYPE_WARNING = 'warning';

    /**#@- */

    /**#@+
     * Success HTTP response codes.
     */
    const HTTP_OK = 200;

    /**#@-*/

    /**
     * Messages.
     *
     * @var array
     */
    protected $_messages = array();

    /**
     * Set header appropriate to specified MIME type.
     *
     * @param string $mimeType MIME type
     * @return $this
     */
    public function setMimeType($mimeType)
    {
        return $this->setHeader('Content-Type', "{$mimeType}; charset=" . self::RESPONSE_CHARSET, true);
    }

    /**
     * Add message to response.
     *
     * @param string $message
     * @param string $code
     * @param array $params
     * @param string $type
     * @return $this
     */
    public function addMessage($message, $code, $params = array(), $type = self::MESSAGE_TYPE_ERROR)
    {
        $params['message'] = $message;
        $params['code'] = $code;
        $this->_messages[$type][] = $params;
        return $this;
    }

    /**
     * Has messages.
     *
     * @return bool
     */
    public function hasMessages()
    {
        return (bool)count($this->_messages) > 0;
    }

    /**
     * Return messages.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Clear messages.
     *
     * @return $this
     */
    public function clearMessages()
    {
        $this->_messages = array();
        return $this;
    }
}
