<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\InventoryShipping\Model\IsMultiSourceMode;

/**
 * Update order_ship button to redirect to Source Selection page
 *
 * @api
 */
class ShipButton extends Container
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var IsMultiSourceMode
     */
    private $isMultiSourceMode;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param IsMultiSourceMode $isMultiSourceMode
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        IsMultiSourceMode $isMultiSourceMode,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->isMultiSourceMode = $isMultiSourceMode;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $order = $this->registry->registry('current_order');
        $websiteId = (int)$order->getStore()->getWebsiteId();
        if ($this->isMultiSourceMode->execute($websiteId)) {
            $this->buttonList->update(
                'order_ship',
                'onclick',
                'setLocation(\'' . $this->getSourceSelectionUrl() . '\')'
            );
        }
        return $this;
    }

    /**
     * Source Selection URL getter
     *
     * @return string
     */
    public function getSourceSelectionUrl()
    {
        return $this->getUrl(
            'inventoryshipping/SourceSelection/index',
            [
                'order_id' => $this->getRequest()->getParam('order_id')
            ]
        );
    }
}
