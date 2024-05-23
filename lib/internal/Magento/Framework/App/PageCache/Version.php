<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\PageCache;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\App\Request\Http;

/**
 * PageCache Version
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Version
{
    /**
     * Name of cookie that holds private content version
     */
    public const COOKIE_NAME = 'private_content_version';

    /**
     * Ten years cookie period
     */
    public const COOKIE_PERIOD = 315360000;

    /**
     * Config setting for disabling session for GraphQl
     */
    private const XML_PATH_GRAPHQL_DISABLE_SESSION = 'graphql/session/disable';

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Http $request
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly CookieManagerInterface $cookieManager,
        private readonly CookieMetadataFactory $cookieMetadataFactory,
        private readonly Http $request,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Generate unique version identifier
     *
     * @return string
     */
    protected function generateValue(): string
    {
        //phpcs:ignore
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
    public function process(): void
    {
        if (!$this->request->isPost()) {
            return;
        }

        if ($this->request->getOriginalPathInfo() === '/graphql' && $this->isSessionDisabled() === true) {
            return;
        }

        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration(self::COOKIE_PERIOD)
            ->setPath('/')
            ->setSecure($this->request->isSecure())
            ->setHttpOnly(false)
            ->setSameSite('Lax');
        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $this->generateValue(), $publicCookieMetadata);
    }

    /**
     * Returns configuration setting for disable session for GraphQl
     *
     * @return bool
     */
    private function isSessionDisabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_GRAPHQL_DISABLE_SESSION,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }
}
