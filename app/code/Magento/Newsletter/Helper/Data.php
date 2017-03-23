<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter Data Helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Helper;

class Data
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_frontendUrlBuilder;

    /**
     * @param \Magento\Framework\UrlInterface $frontendUrlBuilder
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
