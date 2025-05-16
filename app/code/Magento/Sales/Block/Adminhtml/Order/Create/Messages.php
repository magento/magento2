<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

/**
 * Order create errors block
 *
 * @api
 * @since 100.0.2
 */
class Messages extends \Magento\Framework\View\Element\Messages
{

    private const ITEMS_GRID = 'items_grid';

    /**
     * Preparing global layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->addMessages($this->messageManager->getMessages(true));
        $itemsBlock = $this->getLayout()->getBlock(self::ITEMS_GRID);
        if (!$itemsBlock) {
            return;
        }
        $items = $itemsBlock->getItems();
        foreach ($items as $item) {
            if ($item->getHasError()) {
                $messageCollection = $this->getMessageCollection();
                foreach ($messageCollection->getItems() as $blockMessage) {
                    if ($item->getMessage(true) === $blockMessage->getText()) {
                        /* Remove duplicated messages.*/
                        $messageCollection->deleteMessageByIdentifier($blockMessage->getIdentifier());
                    }
                }
                $this->setMessages($messageCollection);
            }
        }

        parent::_prepareLayout();
    }
}
