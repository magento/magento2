<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\System\Message;

use Magento\Framework\Controller\ResultFactory;

class ListAction extends \Magento\Backend\App\AbstractAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_AdminNotification::show_list';

    /**
     * @var \Magento\Framework\Json\Helper\Data
     * @deprecated 100.3.0
     */
    protected $jsonHelper;

    /**
     * @var \Magento\AdminNotification\Model\ResourceModel\System\Message\Collection
     */
    protected $messageCollection;

    /**
     * Initialize ListAction
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\AdminNotification\Model\ResourceModel\System\Message\Collection $messageCollection
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\AdminNotification\Model\ResourceModel\System\Message\Collection $messageCollection
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->messageCollection = $messageCollection;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $severity = $this->getRequest()->getParam('severity');
        if ($severity) {
            $this->messageCollection->setSeverity($severity);
        }
        $result = [];
        foreach ($this->messageCollection->getItems() as $item) {
            $result[] = [
                'severity' => $item->getSeverity(),
                'text' => $item->getText(),
            ];
        }
        if (empty($result)) {
            $result[] = [
                'severity' => (string)\Magento\Framework\Notification\MessageInterface::SEVERITY_NOTICE,
                'text' => __(
                    'You have viewed and resolved all recent system notices. '
                    . 'Please refresh the web page to clear the notice alert.'
                )
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}
