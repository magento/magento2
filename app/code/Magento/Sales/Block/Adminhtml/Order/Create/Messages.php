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
            // Disabled items are removed on quote reload, so their global errors must remain available.
            if ($item->getHasError() && !$item->getProduct()->isDisabled()) {
                $messageCollection = $this->collectionFactory->create();
                foreach ($this->getMessageCollection()->getItems() as $blockMessage) {
                    if ($item->getMessage(true) !== $blockMessage->getText()) {
                        $messageCollection->addMessage($blockMessage);
                    }
                }
                $this->setMessages($messageCollection);
            }
        }

        parent::_prepareLayout();
    }
}
