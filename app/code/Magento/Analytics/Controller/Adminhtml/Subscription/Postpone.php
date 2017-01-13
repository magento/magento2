<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Controller\Adminhtml\Subscription;

use Magento\Analytics\Model\NotificationTime;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class Postpone
 */
class Postpone extends Action
{
    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var NotificationTime
     */
    private $notificationTime;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Postpone constructor.
     *
     * @param Context $context
     * @param DateTimeFactory $dateTimeFactory
     * @param NotificationTime $notificationTime
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        DateTimeFactory $dateTimeFactory,
        NotificationTime $notificationTime,
        LoggerInterface $logger
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->notificationTime = $notificationTime;
        $this->logger = $logger;
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
     * Postpones notification about subscription
     *
     * @return Json
     */
    public function execute()
    {
        try {
            $dateTime = $this->dateTimeFactory->create();
            $responseContent = [
                'success' => $this->notificationTime->storeLastTimeNotification($dateTime->getTimestamp()),
                'error_message' => ''
            ];
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
            $responseContent = [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $responseContent = [
                'success' => false,
                'error_message' => __('Error occurred during postponement notification')
            ];
        }
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($responseContent);
    }
}
