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
namespace Magento\Framework\App\Response;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\CookieManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\App\Http\Context;

class Http extends \Zend_Controller_Response_Http implements HttpInterface
{
    /**
     * Cookie to store page vary string
     */
    const COOKIE_VARY_STRING = 'X-Magento-Vary';

    /**
     * @var \Magento\Framework\Stdlib\CookieManager
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $context;

    /**
     * @param \Magento\Framework\Stdlib\CookieManager $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\Http\Context $context
     */
    public function __construct(
        CookieManager $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Context $context
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->context = $context;
    }

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
     * Send Vary coookie
     *
     * @return void
     */
    public function sendVary()
    {

        $data = $this->context->getData();
        if (!empty($data)) {
            ksort($data);
            $cookieValue = sha1(serialize($data));
            $sensitiveCookMetadata = $this->cookieMetadataFactory->createSensitiveCookieMetadata()
                ->setPath('/');
            $this->cookieManager->setSensitiveCookie(self::COOKIE_VARY_STRING, $cookieValue, $sensitiveCookMetadata);
        } else {
            $cookieMetadata = $this->cookieMetadataFactory->createCookieMetadata()
                ->setPath('/');
            $this->cookieManager->deleteCookie(self::COOKIE_VARY_STRING, $cookieMetadata);
        }
    }

    /**
     * Send the response, including all headers, rendering exceptions if so
     * requested.
     *
     * @return void
     */
    public function sendResponse()
    {
        $this->sendVary();
        parent::sendResponse();
    }

    /**
     * Set headers for public cache
     * Accepts the time-to-live (max-age) parameter
     *
     * @param int $ttl
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setPublicHeaders($ttl)
    {
        if ($ttl < 0 || !preg_match('/^[0-9]+$/', $ttl)) {
            throw new \InvalidArgumentException('Time to live is a mandatory parameter for set public headers');
        }
        $this->setHeader('pragma', 'cache', true);
        $this->setHeader('cache-control', 'public, max-age=' . $ttl . ', s-maxage=' . $ttl, true);
        $this->setHeader('expires', gmdate('D, d M Y H:i:s T', strtotime('+' . $ttl . ' seconds')), true);
    }

    /**
     * Set headers for private cache
     *
     * @param int $ttl
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setPrivateHeaders($ttl)
    {
        if (!$ttl) {
            throw new \InvalidArgumentException('Time to live is a mandatory parameter for set private headers');
        }
        $this->setHeader('pragma', 'cache', true);
        $this->setHeader('cache-control', 'private, max-age=' . $ttl, true);
        $this->setHeader('expires', gmdate('D, d M Y H:i:s T', strtotime('+' . $ttl . ' seconds')), true);
    }

    /**
     * Set headers for no-cache responses
     *
     * @return void
     */
    public function setNoCacheHeaders()
    {
        $this->setHeader('pragma', 'no-cache', true);
        $this->setHeader('cache-control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        $this->setHeader('expires', gmdate('D, d M Y H:i:s T', strtotime('-1 year')), true);
    }

    /**
     * Represents an HTTP response body in JSON format by sending appropriate header
     *
     * @param string $content String in JSON format
     * @return \Magento\Framework\App\Response\Http
     */
    public function representJson($content)
    {
        $this->setHeader('Content-Type', 'application/json', true);
        return $this->setBody($content);
    }

    /**
     * @return string[]
     */
    public function __sleep()
    {
        return ['_body', '_exceptions', '_headers', '_headersRaw', '_httpResponseCode', 'context'];
    }

    /**
     * Need to reconstruct dependencies when being de-serialized.
     *
     * @return void
     */
    public function __wakeup()
    {
        $objectManager = ObjectManager::getInstance();
        $this->cookieManager = $objectManager->create('Magento\Framework\Stdlib\CookieManager');
        $this->cookieMetadataFactory = $objectManager->get('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory');
    }
}
