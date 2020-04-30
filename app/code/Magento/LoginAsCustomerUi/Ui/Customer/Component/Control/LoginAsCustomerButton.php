<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerUi\Ui\Customer\Component\Control;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;

/**
 * Login As Customer button UI component.
 */
class LoginAsCustomerButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $authorization;

    /**
     * Escaper
     *
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        parent::__construct($context, $registry);
        $this->authorization = $context->getAuthorization();
        $this->escaper = $context->getEscaper();
    }

    /**
     * @inheritdoc
     */
    public function getButtonData(): array
    {
        $customerId = $this->getCustomerId();
        $data = [];
        $canModify = $customerId && $this->authorization->isAllowed('Magento_LoginAsCustomer::login_button');
        if ($canModify) {
            $data = [
                'label' => __('Login As Customer'),
                'class' => 'login login-button',
                'on_click' => 'window.lacConfirmationPopup("'
                    . $this->escaper->escapeHtml($this->escaper->escapeJs($this->getLoginUrl()))
                    . '")',
                'sort_order' => 70,
            ];
        }

        return $data;
    }

    /**
     * Get Login As Customer login url.
     *
     * @return string
     */
    public function getLoginUrl(): string
    {
        return $this->getUrl('loginascustomer/login/login', ['customer_id' => $this->getCustomerId()]);
    }
}
