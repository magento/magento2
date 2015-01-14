<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\System\Message;

class ListAction extends \Magento\Backend\App\AbstractAction
{
    /**
     * @return void
     */
    public function execute()
    {
        $severity = $this->getRequest()->getParam('severity');
        $messageCollection = $this->_objectManager->get(
            'Magento\AdminNotification\Model\Resource\System\Message\Collection'
        );
        if ($severity) {
            $messageCollection->setSeverity($severity);
        }
        $result = [];
        foreach ($messageCollection->getItems() as $item) {
            $result[] = ['severity' => $item->getSeverity(), 'text' => $item->getText()];
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }
}
