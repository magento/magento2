<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Customer front  newsletter manage block
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.0.0
 */
class Newsletter extends \Magento\Customer\Block\Account\Dashboard
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'form/newsletter.phtml';

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsSubscribed()
    {
        return $this->getSubscriptionObject()->isSubscribed();
    }

    /**
     * Return the save action Url.
     *
     * @return string
     * @since 2.0.0
     */
    public function getAction()
    {
        return $this->getUrl('newsletter/manage/save');
    }
}
