<?php
/**
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
 * @category   Varien
 * @package    Varien_Http
 * @copyright  Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Varien HTTP Client
 *
 * @category   Varien
 * @package    Varien_Http
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Varien_Http_Client extends Zend_Http_Client
{
    /**
     * Internal flag to allow decoding of request body
     *
     * @var bool
     */
    protected $_urlEncodeBody = true;

    public function __construct($uri = null, $config = null)
    {
        $this->config['useragent'] = 'Varien_Http_Client';

        parent::__construct($uri, $config);
    }

    protected function _trySetCurlAdapter()
    {
        if (extension_loaded('curl')) {
            $this->setAdapter(new Varien_Http_Adapter_Curl());
        }
        return $this;
    }

    public function request($method = null)
    {
        $this->_trySetCurlAdapter();
        return parent::request($method);
    }

    /**
     * Change value of internal flag to disable/enable custom prepare functionality
     *
     * @param bool $flag
     * @return Varien_Http_Client
     */
    public function setUrlEncodeBody($flag)
    {
        $this->_urlEncodeBody = $flag;
        return $this;
    }

    /**
     * Adding custom functionality to decode data after
     * standard prepare functionality
     *
     * @return string
     */
    protected function _prepareBody()
    {
        $body = parent::_prepareBody();

        if (!$this->_urlEncodeBody && $body) {
            $body = urldecode($body);
            $this->setHeaders('Content-length', strlen($body));
        }

        return $body;
    }
}
