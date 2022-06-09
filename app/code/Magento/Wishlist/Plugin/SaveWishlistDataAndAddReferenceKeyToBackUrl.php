<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Wishlist\Plugin;

use Magento\Wishlist\Model\DataSerializer;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\UrlInterface;

/**
 * Cache wishlist data & Modify back Url
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class SaveWishlistDataAndAddReferenceKeyToBackUrl
{
    /**
     * @var DataSerializer
     */
    private $dataSerializer;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param DataSerializer $dataSerializer
     * @param CustomerSession $customerSession
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        DataSerializer $dataSerializer,
        CustomerSession $customerSession,
        UrlInterface $urlBuilder
    ) {
        $this->dataSerializer = $dataSerializer;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Before sending New Account Email, Cache wishlist data & Modify back Url
     *
     * @param EmailNotificationInterface $subject
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int $storeId
     * @param string $sendemailStoreId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeNewAccount(
        EmailNotificationInterface $subject,
        CustomerInterface $customer,
        $type = EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = null,
        $sendemailStoreId = null
    ): array {
        if ($this->customerSession->getBeforeWishlistRequest() != null
            && $customer->getConfirmation() != null
        ) {
            $token = $this->dataSerializer->serialize($this->customerSession->getBeforeWishlistRequest());
            $backUrl = $this->urlBuilder->getUrl('wishlist/index/add', ['_query' => ['token' => $token]]);
        }

        return [$customer, $type, $backUrl, $storeId, $sendemailStoreId];
    }
}
