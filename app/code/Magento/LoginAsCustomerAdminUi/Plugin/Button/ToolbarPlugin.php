<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAdminUi\Plugin\Button;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\Escaper;
use Magento\Framework\AuthorizationInterface;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;

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
     * @var ConfigInterface
     */
    private $config;

    /**
     * ToolbarPlugin constructor.
     * @param AuthorizationInterface $authorization
     * @param ConfigInterface $config
     * @param Escaper $escaper
     */
    public function __construct(
        AuthorizationInterface $authorization,
        ConfigInterface $config,
        Escaper $escaper
    ) {
        $this->authorization = $authorization;
        $this->config = $config;
        $this->escaper = $escaper;
    }

    /**
     * Add Login as Customer button.
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

            $isAllowed = $this->authorization->isAllowed('Magento_LoginAsCustomer::login_button');
            $isEnabled = $this->config->isEnabled();
            if ($isAllowed && $isEnabled) {
                if (!empty($order['customer_id'])) {
                    $buttonUrl = $context->getUrl('loginascustomer/login/login', [
                        'customer_id' => $order['customer_id']
                    ]);
                    $buttonList->add(
                        'guest_to_customer',
                        [
                            'label' => __('Login as Customer'),
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
}
