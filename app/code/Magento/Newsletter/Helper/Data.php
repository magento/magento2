<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter Data Helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Retrieve subsription confirmation url
     *
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @return string
     */
    public function getConfirmationUrl($subscriber)
    {
        return $this->_urlBuilder->setScope(
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
        return $this->_urlBuilder->setScope(
            $subscriber->getStoreId()
        )->getUrl(
            'newsletter/subscriber/unsubscribe',
            ['id' => $subscriber->getId(), 'code' => $subscriber->getCode(), '_nosid' => true]
        );
    }
}
