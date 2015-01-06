<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
