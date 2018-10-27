<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Controller\Adminhtml\Notification;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\ReleaseNotification\Model\ResourceModel\Viewer\Logger as NotificationLogger;
use Magento\Framework\App\ProductMetadataInterface;
use Psr\Log\LoggerInterface;

/**
 * Class MarkUserNotified
 */
class MarkUserNotified extends Action
{
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
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->productMetadata = $productMetadata;
        $this->notificationLogger = $notificationLogger;
        $this->logger = $logger;
    }

    /**
     * Log information about the last shown advertisement
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $responseContent = [
                'success' => $this->notificationLogger->log(
                    $this->_auth->getUser()->getId(),
                    $this->productMetadata->getVersion()
                ),
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
                'error_message' => __('It is impossible to log user action')
            ];
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($responseContent);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed();
    }
}
