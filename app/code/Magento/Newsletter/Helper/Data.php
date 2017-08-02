<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Helper;

/**
 * Newsletter Data Helper
 *
 * @api
 * @since 2.0.0
 */
class Data
{
    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $_frontendUrlBuilder;

    /**
     * @param \Magento\Framework\UrlInterface $frontendUrlBuilder
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\UrlInterface $frontendUrlBuilder)
    {
        $this->_frontendUrlBuilder = $frontendUrlBuilder;
    }

    /**
     * Retrieve subsription confirmation url
     *
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @return string
     * @since 2.0.0
     */
    public function getConfirmationUrl($subscriber)
    {
        return $this->_frontendUrlBuilder->setScope(
            $subscriber->getStoreId()
        )->getUrl(
            'newsletter/subscriber/confirm',
            ['id' => $subscriber->getId(), 'code' => $subscriber->getCode(), '_nosid' => true]
        );
    }

    /**
     * Retrieve unsubsription url
     *
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @return string
     * @since 2.0.0
     */
    public function getUnsubscribeUrl($subscriber)
    {
        return $this->_frontendUrlBuilder->setScope(
            $subscriber->getStoreId()
        )->getUrl(
            'newsletter/subscriber/unsubscribe',
            ['id' => $subscriber->getId(), 'code' => $subscriber->getCode(), '_nosid' => true]
        );
    }
}
