<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Block\Adminhtml\Create;

use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Sales\Helper\Admin;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment as OrderShipment;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Adminhtml shipment create form
 *
 * @api
 * @since 100.0.2
 */
class Form extends AbstractOrder
{
    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param array $data
     * @param TaxHelper|null $taxHelper
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        Admin $adminHelper,
        array $data = [],
        ?TaxHelper $taxHelper = null
    ) {
        $data['taxHelper'] = $taxHelper ?? ObjectManager::getInstance()->get(TaxHelper::class);
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * Retrieve invoice order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->getShipment()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return OrderShipment
     */
    public function getSource()
    {
        return $this->getShipment();
    }

    /**
     * Retrieve shipment model instance
     *
     * @return OrderShipment
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

    /**
     * Prepare layout.
     *
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->addChild('items', Items::class);
        return parent::_prepareLayout();
    }

    /**
     * Return payment html.
     *
     * @return string
     */
    public function getPaymentHtml()
    {
        return $this->getChildHtml('order_payment');
    }

    /**
     * Return items html.
     *
     * @return string
     */
    public function getItemsHtml()
    {
        return $this->getChildHtml('order_items');
    }

    /**
     * Generate save url.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', ['order_id' => $this->getShipment()->getOrderId()]);
    }
}
