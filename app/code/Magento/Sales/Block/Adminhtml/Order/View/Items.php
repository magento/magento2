<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\View;

use Magento\Sales\Model\Resource\Order\Item\Collection;

/**
 * Adminhtml order items grid
 */
class Items extends \Magento\Sales\Block\Adminhtml\Items\AbstractItems
{
    /**
     * Retrieve required options from parent
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            throw new \Magento\Framework\Model\Exception(__('Invalid parent block for this block'));
        }
        $this->setOrder($this->getParentBlock()->getOrder());
        parent::_beforeToHtml();
    }

    /**
     * Retrieve order items collection
     *
     * @return Collection
     */
    public function getItemsCollection()
    {
        return $this->getOrder()->getItemsCollection();
    }
}
