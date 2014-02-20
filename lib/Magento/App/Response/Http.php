<?php
/**
 * HTTP response
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
namespace Magento\App\Response;

class Http extends \Zend_Controller_Response_Http implements \Magento\App\ResponseInterface
{
    /**
     * Cookie to store page vary string
     */
    const COOKIE_VARY_STRING = 'X-Magento-Vary';

    /**
     * Response vary identifiers
     *
     * @var array
     */
    protected $vary;

    /**
     * Get header value by name.
     * Returns first found header by passed name.
     * If header with specified name was not found returns false.
     *
     * @param string $name
     * @return array|bool
     */
    public function getHeader($name)
    {
        foreach ($this->_headers as $header) {
            if ($header['name'] == $name) {
                return $header;
            }
        }
        return false;
    }

    /**
     * Set vary identifier
     *
     * @param string $name
     * @param string|array $value
     * @return $this
     */
    public function setVary($name, $value)
    {
        if (is_array($value)) {
            $value = serialize($value);
        }
        $this->vary[$name] = $value;
        return $this;
    }

    /**
     * Send the response, including all headers, rendering exceptions if so
     * requested.
     *
     * @return void
     */
    public function sendResponse()
    {
        setcookie(self::COOKIE_VARY_STRING, $this->getVaryString(), null, '/');
        parent::sendResponse();
    }

    /**
     * Returns hash of varies
     *
     * @return string
     */
    public function getVaryString()
    {
        if (!empty($this->vary)) {
            ksort($this->vary);
        }

        return sha1(serialize($this->vary));
    }
}
