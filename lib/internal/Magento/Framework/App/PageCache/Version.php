<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

/**
 * Class Version
 *
 */
class Version
{
    /**
     * Name of cookie that holds private content version
     */
    const COOKIE_NAME = 'private_content_version';

    /**
     * Ten years cookie period
     */
    const COOKIE_PERIOD = 315360000;

    /**
     * Cookie Manager
     *
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->cookieManager = $cookieManager;
        $this->request = $request;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * Generate unique version identifier
     *
     * @return string
     */
    protected function generateValue()
    {
        return md5(rand() . time());
    }

    /**
     * Handle private content version cookie
     * Set cookie if it is not set.
     * Increment version on post requests.
     * In all other cases do nothing.
     *
     * @return void
     */
    public function process()
    {
        if ($this->request->isPost()) {
            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setDuration(self::COOKIE_PERIOD)
                ->setPath('/')
                ->setSecure($this->request->isSecure())
                ->setHttpOnly(false);
            $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $this->generateValue(), $publicCookieMetadata);
        }
    }
}
