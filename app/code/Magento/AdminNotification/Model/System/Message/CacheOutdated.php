<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\System\Message;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;

/**
 * Class CacheOutdated
 *
 * @package Magento\AdminNotification\Model\System\Message
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class CacheOutdated implements MessageInterface
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder; //phpcs:ignore

    /**
     * @var AuthorizationInterface
     */
    protected $_authorization; //phpcs:ignore

    /**
     * @var TypeListInterface
     */
    protected $_cacheTypeList; //phpcs:ignore

    /**
     * @param AuthorizationInterface $authorization
     * @param UrlInterface $urlBuilder
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        AuthorizationInterface $authorization,
        UrlInterface $urlBuilder,
        TypeListInterface $cacheTypeList
    ) {
        $this->_authorization = $authorization;
        $this->_urlBuilder = $urlBuilder;
        $this->_cacheTypeList = $cacheTypeList;
    }

    /**
     * Get array of cache types which require data refresh
     *
     * @return array
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _getCacheTypesForRefresh(): array //phpcs:ignore
    {
        $output = [];
        foreach ($this->_cacheTypeList->getInvalidated() as $type) {
            $output[] = $type->getCacheType();
        }
        return $output;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity(): string
    {
        return md5('cache' . implode(':', $this->_getCacheTypesForRefresh()));
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed(): bool
    {
        return $this->_authorization->isAllowed(
            'Magento_Backend::cache'
        ) && count(
            $this->_getCacheTypesForRefresh()
        ) > 0;
    }

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText(): string
    {
        $cacheTypes = implode(', ', $this->_getCacheTypesForRefresh());
        $message = __('One or more of the Cache Types are invalidated: %1. ', $cacheTypes) . ' ';
        $url = $this->_urlBuilder->getUrl('adminhtml/cache');
        $message .= __('Please go to <a href="%1">Cache Management</a> and refresh cache types.', $url);
        return $message;
    }

    /**
     * Retrieve problem management url
     *
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->_urlBuilder->getUrl('adminhtml/cache');
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity(): int
    {
        return MessageInterface::SEVERITY_CRITICAL;
    }
}
