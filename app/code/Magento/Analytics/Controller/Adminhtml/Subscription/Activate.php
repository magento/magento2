<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Controller\Adminhtml\Subscription;

use Magento\Analytics\Model\Config\Backend\Enabled;
use Magento\Analytics\Model\NotificationTime;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Psr\Log\LoggerInterface;

/**
 * Activates subscription to Magento BI Advanced Reporting.
 */
class Activate extends Action
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Resource for managing last notification time about subscription to Magento BI.
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
     * @var AbstractDb
     */
    private $configValueResource;

    /**
     * @var PreparedValueFactory
     */
    private $preparedValueFactory;

    /**
     * Activate constructor.
     *
     * @param Context $context
     * @param LoggerInterface $logger
     * @param NotificationTime $notificationTime
     * @param AbstractDb $configValueResource
     * @param PreparedValueFactory $preparedValueFactory
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        NotificationTime $notificationTime,
        AbstractDb $configValueResource,
        PreparedValueFactory $preparedValueFactory
    ) {
        $this->logger = $logger;
        $this->notificationTime = $notificationTime;
        $this->configValueResource = $configValueResource;
        $this->preparedValueFactory = $preparedValueFactory;
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
     * Activate subscription to Magento BI via AJAX.
     *
     * @return Json
     */
    public function execute()
    {
        try {
            if ($this->getRequest()->getParam($this->subscriptionApprovedField)) {
                $configValue = $this->preparedValueFactory->create(
                    Enabled::XML_ENABLED_CONFIG_STRUCTURE_PATH,
                    Enabledisable::ENABLE_VALUE,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                );

                $this->configValueResource
                    ->save($configValue);
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
