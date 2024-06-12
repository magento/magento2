<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order\Create;

use Magento\Backend\Block\Widget\Button;
use Magento\Framework\DataObject;

/**
 * Adminhtml sales order create sidebar
 *
 * @api
 * @since 100.0.2
 */
class Sidebar extends AbstractCreate
{
    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->getCustomerId()) {
            $button = $this->getLayout()->createBlock(
                Button::class
            )->setData(
                [
                    'label' => __('Update Changes'),
                    'onclick' => 'order.sidebarApplyChanges()',
                    'class' => 'action-secondary',
                    'before_html' => '<div class="actions">',
                    'after_html' => '</div>',
                ]
            );
            $this->setChild('top_button', $button);

            $button = clone $button;
            $button->unsId();
            $this->setChild('bottom_button', $button);
        }
        return parent::_prepareLayout();
    }

    /**
     * Check if can display
     *
     * @param DataObject $child
     * @return true
     */
    public function canDisplay($child)
    {
        if (method_exists($child, 'canDisplay') && is_callable([$child, 'canDisplay'])) {
            return $child->canDisplay();
        }
        return true;
    }

    /**
     * To check customer permission
     *
     * @return bool
     */
    public function isAllowedAction(): bool
    {
        return $this->_authorization->isAllowed('Magento_Customer::customer');
    }
}
