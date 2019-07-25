<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminAnalytics\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\AdminAnalytics\Model\ResourceModel\Viewer\Logger as NotificationLogger;
use Magento\Framework\App\ProductMetadataInterface;
use Psr\Log\LoggerInterface;
use Magento\Config\Model\Config\Factory;

/**
 * Controller to record that the current admin user has seen the release notification content
 */
class DisableAdminUsage extends Action
{


    private $configFactory;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var NotificationLogger
     */
    private $notificationLogger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MarkUserNotified constructor.
     *
     * @param Action\Context $context
     * @param ProductMetadataInterface $productMetadata
     * @param NotificationLogger $notificationLogger
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        ProductMetadataInterface $productMetadata,
        NotificationLogger $notificationLogger,
        Factory $configFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->configFactory = $configFactory;
        $this->productMetadata = $productMetadata;
        $this->notificationLogger = $notificationLogger;
        $this->logger = $logger;
    }
    public function disableAdminUsage()
    {
        $configModel = $this->configFactory->create();
        $configModel->setDataByPath('admin/usage/enabled', 0);
        $configModel->save();
    }

    public function markUserNotified()
    {
        $responseContent = [
            'success' => $this->notificationLogger->log(
                $this->_auth->getUser()->getId(),
                $this->productMetadata->getVersion(),
                0
            ),
            'error_message' => ''
        ];

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($responseContent);
    }
    /**
     * Log information about the last shown advertisement
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->disableAdminUsage();
        $this->markUserNotified();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed();
    }
}
