<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Exception;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Psr\Log\LoggerInterface;

/**
 * Service for managing compare list cookies
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CompareCookieManager
{
    /**
     * Name of cookie that holds compare products section data
     */
    public const COOKIE_COMPARE_PRODUCTS = 'section_data_ids';

    /**
     * The path for which the cookie will be available
     */
    public const COOKIE_PATH = '/';

    /**
     * Cookie lifetime value in seconds (86400 = 24 hours)
     */
    public const COOKIE_LIFETIME = 86400;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CookieManagerInterface $cookieManager,
        private readonly CookieMetadataFactory  $cookieMetadataFactory,
        private readonly LoggerInterface        $logger
    ) {
    }

    /**
     * Marks compare products section as invalid by updating the cookie value
     *
     * @return void
     */
    public function invalidate(): void
    {
        try {
            $cookieValue = json_encode(['compare-products' => time()]);
            $this->setCookie($cookieValue);
        } catch (Exception $e) {
            $this->logger->error('Error invalidating compare products cookie: ' . $e->getMessage());
        }
    }

    /**
     * Set compare products cookie
     *
     * @param string $value
     * @return void
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    private function setCookie(string $value): void
    {
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration(self::COOKIE_LIFETIME)
            ->setPath(self::COOKIE_PATH)
            ->setHttpOnly(false);

        $this->cookieManager->setPublicCookie(
            self::COOKIE_COMPARE_PRODUCTS,
            $value,
            $publicCookieMetadata
        );
    }
}
