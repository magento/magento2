<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\ViewModel\CreditMemo\Create;

use Magento\Backend\Block\Widget\Button;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items;

/**
 * View model to add Update Totals button for new Credit Memo
 */
class UpdateTotalsButton implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var Items
     */
    private $items;

    /**
     * @param LayoutInterface $layout
     * @param Items $items
     */
    public function __construct(
        LayoutInterface $layout,
        Items $items
    ) {
        $this->layout = $layout;
        $this->items = $items;
    }

    /**
     * Get Update Totals block html.
     *
     * @return string
     */
    public function getUpdateTotalsButton(): string
    {
        $block = $this->createUpdateTotalsBlock();

        return $block->toHtml();
    }

    /**
     * Create Update Totals block.
     *
     * @return BlockInterface
     */
    private function createUpdateTotalsBlock(): BlockInterface
    {
        $onclick = "submitAndReloadArea($('creditmemo_item_container'),'" . $this->items->getUpdateUrl() . "')";
        $block = $this->layout->addBlock(Button::class, 'update_totals_button', 'order_items');
        $block->setData(
            [
                'label' => __('Update Totals'),
                'class' => 'update-totals-button secondary',
                'onclick' => $onclick,
            ]
        );

        return $block;
    }
}
