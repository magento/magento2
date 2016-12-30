<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Controller\Adminhtml\Subscription;


use Magento\Analytics\Model\Subscription;
use \Magento\Framework\App\Action\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

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
     * @param Context $context
     * @param Subscription $subscription
     * @param LoggerInterface $logger
     */
    public function __construct(Context $context, Subscription $subscription, LoggerInterface $logger)
    {
        $this->subscription = $subscription;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Activate subscription to Magento Analytics via AJAX.
     *
     * @return Json
     */
    public function execute()
    {
        try {
            $this->subscription->enable();
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
                'error_message' => __('Sorry, something went wrong.'),
            ];
            $this->logger->error($e->getMessage());
        }
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($responseContent);
    }
}
