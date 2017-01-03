<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Controller\Adminhtml\Subscription;


use Magento\Analytics\Model\NotificationTime;
use Magento\Framework\App\Action\Action;
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
     * Postpones notification about subscription
     *
     * @return Json
     */
    public function execute()
    {
        try {
            $dateTime = $this->dateTimeFactory->create();
            $this->notificationTime->storeLastTimeNotification($dateTime->getTimestamp());
            $responseContent = [
                'success' => true,
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