<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerUi\Plugin\Button;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\Escaper;
use Magento\Framework\AuthorizationInterface;

/**
 * Plugin for \Magento\Backend\Block\Widget\Button\Toolbar.
 */
class ToolbarPlugin
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * ToolbarPlugin constructor.
     * @param AuthorizationInterface $authorization
     * @param Escaper $escaper
     */
    public function __construct(
        AuthorizationInterface $authorization,
        Escaper $escaper
    ) {
        $this->authorization = $authorization;
        $this->escaper = $escaper;
    }

    /**
     * Add Login As Customer button.
     *
     * @param \Magento\Backend\Block\Widget\Button\Toolbar $subject
     * @param \Magento\Framework\View\Element\AbstractBlock $context
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePushButtons(
        Toolbar $subject,
        AbstractBlock $context,
        ButtonList $buttonList
    ):void {
        $order = false;
        $nameInLayout = $context->getNameInLayout();

        if ('sales_order_edit' == $nameInLayout) {
            $order = $context->getOrder();
        } elseif ('sales_invoice_view' == $nameInLayout) {
            $order = $context->getInvoice()->getOrder();
        } elseif ('sales_shipment_view' == $nameInLayout) {
            $order = $context->getShipment()->getOrder();
        } elseif ('sales_creditmemo_view' == $nameInLayout) {
            $order = $context->getCreditmemo()->getOrder();
        }
        if ($order) {
            if ($this->isAllowed()) {
                if (!empty($order['customer_id'])) {
                    $buttonUrl = $context->getUrl('loginascustomer/login/login', [
                        'customer_id' => $order['customer_id']
                    ]);
                    $buttonList->add(
                        'guest_to_customer',
                        [
                            'label' => __('Login As Customer'),
                            'onclick' => 'window.lacConfirmationPopup("'
                                . $this->escaper->escapeHtml($this->escaper->escapeJs($buttonUrl))
                                . '")',
                            'class' => 'reset'
                        ],
                        -1
                    );
                }
            }
        }
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    private function isAllowed(): bool
    {
        return (bool)$this->authorization->isAllowed('Magento_LoginAsCustomer::login_button');
    }
}
