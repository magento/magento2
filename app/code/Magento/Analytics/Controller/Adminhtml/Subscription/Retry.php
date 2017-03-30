<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Controller\Adminhtml\Subscription;

use Magento\Analytics\Model\Subscription;
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
     * @var Subscription
     */
    private $subscription;

    /**
     * @param Context $context
     * @param Subscription $subscription
     */
    public function __construct(
        Context $context,
        Subscription $subscription
    ) {
        $this->subscription = $subscription;
        parent::__construct($context);
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Analytics::analytics_settings');
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
            $this->subscription->retry();
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
