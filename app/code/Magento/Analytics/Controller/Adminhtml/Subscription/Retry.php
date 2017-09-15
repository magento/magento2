<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Controller\Adminhtml\Subscription;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Retry subscription to Magento BI Advanced Reporting.
 * @since 2.2.0
 */
class Retry extends Action
{
    /**
     * Resource for managing subscription to Magento Analytics.
     *
     * @var SubscriptionHandler
     * @since 2.2.0
     */
    private $subscriptionHandler;

    /**
     * @param Context $context
     * @param SubscriptionHandler $subscriptionHandler
     * @since 2.2.0
     */
    public function __construct(
        Context $context,
        SubscriptionHandler $subscriptionHandler
    ) {
        $this->subscriptionHandler = $subscriptionHandler;
        parent::__construct($context);
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     * @since 2.2.0
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Analytics::analytics_settings');
    }

    /**
     * Retry process of subscription.
     *
     * @return Redirect
     * @since 2.2.0
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $resultRedirect->setPath('adminhtml');
            $this->subscriptionHandler->processEnabled();
        } catch (LocalizedException $e) {
            $this->getMessageManager()->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->getMessageManager()->addExceptionMessage(
                $e,
                __('Sorry, there has been an error processing your request. Please try again later.')
            );
        }

        return $resultRedirect;
    }
}
