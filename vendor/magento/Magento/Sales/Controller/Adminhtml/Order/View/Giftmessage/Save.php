<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\Adminhtml\Order\View\Giftmessage;

class Save extends \Magento\Sales\Controller\Adminhtml\Order\View\Giftmessage
{
    /**
     * @return void
     */
    public function execute()
    {
        try {
            $this->_getGiftmessageSaveModel()->setGiftmessages(
                $this->getRequest()->getParam('giftmessage')
            )->saveAllInOrder();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong while saving the gift message.'));
        }

        if ($this->getRequest()->getParam('type') == 'order_item') {
            $this->getResponse()->setBody($this->_getGiftmessageSaveModel()->getSaved() ? 'YES' : 'NO');
        } else {
            $this->getResponse()->setBody(__('The gift message has been saved.'));
        }
    }
}
