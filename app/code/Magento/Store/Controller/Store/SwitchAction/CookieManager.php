<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Controller\Store\SwitchAction;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Handles store switching cookie for the frontend storage clean
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CookieManager
{
    /**
     * @var string
     */
    const COOKIE_NAME = 'section_data_clean';

    /**
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(
        private readonly CookieMetadataFactory $cookieMetadataFactory,
        private readonly CookieManagerInterface $cookieManager
    ) {
    }

    /**
     * Set cookie for store
     *
     * @param StoreInterface $targetStore
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    public function setCookieForStore(StoreInterface $targetStore)
    {
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setHttpOnly(false)
            ->setDuration(15)
            ->setPath($targetStore->getStorePath());
        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $targetStore->getCode(), $cookieMetadata);
    }
}
