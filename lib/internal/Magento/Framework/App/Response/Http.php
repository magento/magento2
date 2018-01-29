<?php
/**
 * HTTP response
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Session\Config\ConfigInterface;

class Http extends \Magento\Framework\HTTP\PhpEnvironment\Response
{
    /** Cookie to store page vary string */
    const COOKIE_VARY_STRING = 'X-Magento-Vary';

    /** Format for expiration timestamp headers */
    const EXPIRATION_TIMESTAMP_FORMAT = 'D, d M Y H:i:s T';

    /** X-FRAME-OPTIONS Header name */
    const HEADER_X_FRAME_OPT = 'X-Frame-Options';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
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
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface
     */
    private $sessionConfig;

    /**
     * @param HttpRequest $request
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Context $context
     * @param DateTime $dateTime
     * @param ConfigInterface|null $sessionConfig
     */
    public function __construct(
        HttpRequest $request,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Context $context,
        DateTime $dateTime,
        ConfigInterface $sessionConfig = null
    ) {
        $this->request = $request;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->context = $context;
        $this->dateTime = $dateTime;
        $this->sessionConfig = $sessionConfig ?: ObjectManager::getInstance()->get(ConfigInterface::class);
    }

    /**
     * Sends the X-FRAME-OPTIONS header to protect against click-jacking
     *
     * @param string $value
     * @return void
     */
    public function setXFrameOptions($value)
    {
        $this->setHeader(self::HEADER_X_FRAME_OPT, $value);
    }

    /**
     * Send Vary cookie
     *
     * @return void
     */
    public function sendVary()
    {
        $varyString = $this->context->getVaryString();
        if ($varyString) {
            $cookieLifeTime = $this->sessionConfig->getCookieLifetime();
            $sensitiveCookMetadata = $this->cookieMetadataFactory->createSensitiveCookieMetadata(
                [CookieMetadata::KEY_DURATION => $cookieLifeTime]
            )->setPath('/');
            $this->cookieManager->setSensitiveCookie(self::COOKIE_VARY_STRING, $varyString, $sensitiveCookMetadata);
        } elseif ($this->request->get(self::COOKIE_VARY_STRING)) {
            $cookieMetadata = $this->cookieMetadataFactory->createSensitiveCookieMetadata()->setPath('/');
            $this->cookieManager->deleteCookie(self::COOKIE_VARY_STRING, $cookieMetadata);
        }
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
        $this->setHeader('expires', $this->getExpirationHeader('+' . $ttl . ' seconds'), true);
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
        $this->setHeader('expires', $this->getExpirationHeader('+' . $ttl . ' seconds'), true);
    }

    /**
     * Set headers for no-cache responses
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function setNoCacheHeaders()
    {
        $this->setHeader('pragma', 'no-cache', true);
        $this->setHeader('cache-control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        $this->setHeader('expires', $this->getExpirationHeader('-1 year'), true);
    }

    /**
     * Represents an HTTP response body in JSON format by sending appropriate header
     *
     * @param string $content String in JSON format
     * @return \Magento\Framework\App\Response\Http
     * @codeCoverageIgnore
     */
    public function representJson($content)
    {
        $this->setHeader('Content-Type', 'application/json', true);
        return $this->setContent($content);
    }

    /**
     * @return string[]
     * @codeCoverageIgnore
     */
    public function __sleep()
    {
        return ['content', 'isRedirect', 'statusCode', 'context', 'headers'];
    }

    /**
     * Need to reconstruct dependencies when being de-serialized.
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function __wakeup()
    {
        $objectManager = ObjectManager::getInstance();
        $this->cookieManager = $objectManager->create(\Magento\Framework\Stdlib\CookieManagerInterface::class);
        $this->cookieMetadataFactory = $objectManager->get(
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
        );
        $this->request = $objectManager->get(\Magento\Framework\App\Request\Http::class);
    }

    /**
     * Given a time input, returns the formatted header
     *
     * @param string $time
     * @return string
     * @codeCoverageIgnore
     */
    protected function getExpirationHeader($time)
    {
        return $this->dateTime->gmDate(self::EXPIRATION_TIMESTAMP_FORMAT, $this->dateTime->strToTime($time));
    }
}
