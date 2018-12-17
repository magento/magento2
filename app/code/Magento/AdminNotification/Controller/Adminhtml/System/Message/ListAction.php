<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Controller\Adminhtml\System\Message;

use Magento\AdminNotification\Model\ResourceModel\System\Message\Collection;
use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Notification\MessageInterface;

/**
 * Class ListAction
 *
 * @package Magento\AdminNotification\Controller\Adminhtml\System\Message
 */
class ListAction extends AbstractAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_AdminNotification::show_list';

    /**
     * @var Data
     * @deprecated
     */
    protected $jsonHelper;

    /**
     * @var Collection
     */
    protected $messageCollection;

    /**
     * Initialize ListAction
     *
     * @param Context $context
     * @param Data $jsonHelper
     * @param Collection $messageCollection
     */
    public function __construct(
        Context $context,
        Data $jsonHelper,
        Collection $messageCollection
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->messageCollection = $messageCollection;
        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function execute(): Json
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
                'severity' => (string)MessageInterface::SEVERITY_NOTICE,
                'text' => __(
                    'You have viewed and resolved all recent system notices. '
                    . 'Please refresh the web page to clear the notice alert.'
                )
            ];
        }
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}
