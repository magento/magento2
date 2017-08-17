<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\System\Message;

/**
 * Class \Magento\AdminNotification\Controller\Adminhtml\System\Message\ListAction
 *
 */
class ListAction extends \Magento\Backend\App\AbstractAction
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
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
     * @return void
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
                'text' => 'You have viewed and resolved all recent system notices. '
                    . 'Please refresh the web page to clear the notice alert.',
            ];
        }
        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
    }
}
