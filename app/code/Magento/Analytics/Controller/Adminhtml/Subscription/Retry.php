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
 */
class Retry extends Action
{
    /**
     * Resource for managing subscription to Magento Analytics.
     *
     * @var SubscriptionHandler
     */
    private $subscriptionHandler;

    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'Magento_Analytics::analytics_settings';

    /**
     * @param Context $context
     * @param SubscriptionHandler $subscriptionHandler
     */
    public function __construct(
        Context $context,
        SubscriptionHandler $subscriptionHandler
    ) {
        $this->subscriptionHandler = $subscriptionHandler;
        parent::__construct($context);
    }

    /**
     * Retry process of subscription.
     *
     * @return Redirect
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
