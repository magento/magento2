<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Controller\Adminhtml\Subscription;

use Magento\Analytics\Model\NotificationTime;
use Magento\Analytics\Model\Subscription;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class Activate
 *
 * Activates subscription with Free Tier program
 */
class Activate extends Action
{
    /**
     * Resource for managing subscription to Magento Analytics.
     *
     * @var Subscription
     */
    private $subscription;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Resource for managing last notification time about subscription to Magento Analytics.
     *
     * @var NotificationTime
     */
    private $notificationTime;

    /**
     * Agreement on subscription value into request.
     *
     * @var string
     */
    private $subscriptionApprovedField = 'analytics_subscription_checkbox';

    /**
     * Activate constructor.
     *
     * @param Context $context
     * @param Subscription $subscription
     * @param LoggerInterface $logger
     * @param NotificationTime $notificationTime
     */
    public function __construct(
        Context $context,
        Subscription $subscription,
        LoggerInterface $logger,
        NotificationTime $notificationTime
    ) {
        $this->subscription = $subscription;
        $this->logger = $logger;
        $this->notificationTime = $notificationTime;
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
     * Activate subscription to Magento Analytics via AJAX.
     *
     * @return Json
     */
    public function execute()
    {
        try {
            if ($this->getRequest()->getParam($this->subscriptionApprovedField)) {
                $this->subscription->enable();
            } else {
                $this->notificationTime->unsetLastTimeNotificationValue();
            }
            $responseContent = [
                'success' => true,
                'error_message' => '',
            ];
        } catch (LocalizedException $e) {
            $responseContent = [
                'success' => false,
                'error_message' => $e->getMessage(),
            ];
            $this->logger->error($e->getMessage());
        } catch (\Exception $e) {
            $responseContent = [
                'success' => false,
                'error_message' => __(
                    'Sorry, there was an error processing your registration request to Magento Analytics. '
                    . 'Please try again later.'
                ),
            ];
            $this->logger->error($e->getMessage());
        }
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($responseContent);
    }
}
