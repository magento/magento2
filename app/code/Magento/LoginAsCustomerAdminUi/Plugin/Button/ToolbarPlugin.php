<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAdminUi\Plugin\Button;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\LoginAsCustomerAdminUi\Ui\Customer\Component\Button\DataProvider;
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
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * ToolbarPlugin constructor.
     * @param AuthorizationInterface $authorization
     * @param ConfigInterface $config
     * @param Escaper $escaper
     * @param DataProvider $dataProvider
     */
    public function __construct(
        AuthorizationInterface $authorization,
        ConfigInterface $config,
        Escaper $escaper,
        DataProvider $dataProvider
    ) {
        $this->authorization = $authorization;
        $this->config = $config;
        $this->escaper = $escaper;
        $this->dataProvider = $dataProvider;
    }

    /**
     * Add Login as Customer button.
     *
     * @param \Magento\Backend\Block\Widget\Button\ToolbarInterface $subject
     * @param \Magento\Framework\View\Element\AbstractBlock $context
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePushButtons(
        \Magento\Backend\Block\Widget\Button\ToolbarInterface $subject,
        AbstractBlock $context,
        ButtonList $buttonList
    ): void {
        $nameInLayout = $context->getNameInLayout();

        $order = $this->getOrder($nameInLayout, $context);
        if ($order
            && !empty($order['customer_id'])
            && $this->config->isEnabled()
            && $this->authorization->isAllowed('Magento_LoginAsCustomer::login_button')
        ) {
            $customerId = (int)$order['customer_id'];
            $buttonList->add(
                'guest_to_customer',
                $this->dataProvider->getData($customerId),
                -1
            );
        }
    }

    /**
     * Extract order data from context.
     *
     * @param string $nameInLayout
     * @param AbstractBlock $context
     * @return array|null
     */
    private function getOrder(string $nameInLayout, AbstractBlock $context)
    {
        $order = null;

        if ('sales_order_edit' == $nameInLayout) {
            $order = $context->getOrder();
        } elseif ('sales_invoice_view' == $nameInLayout) {
            $order = $context->getInvoice()->getOrder();
        } elseif ('sales_shipment_view' == $nameInLayout) {
            $order = $context->getShipment()->getOrder();
        } elseif ('sales_creditmemo_view' == $nameInLayout) {
            $order = $context->getCreditmemo()->getOrder();
        }

        return $order;
    }
}
