<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAdminUi\Ui\Customer\Component\Control;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;

/**
 * Login as Customer button UI component.
 */
class LoginAsCustomerButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Escaper
     *
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ConfigInterface $config
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ConfigInterface $config
    ) {
        parent::__construct($context, $registry);
        $this->authorization = $context->getAuthorization();
        $this->config = $config;
        $this->escaper = $context->getEscaper();
    }

    /**
     * @inheritdoc
     */
    public function getButtonData(): array
    {
        $customerId = $this->getCustomerId();
        $data = [];
        $isAllowed = $customerId && $this->authorization->isAllowed('Magento_LoginAsCustomer::login_button');
        $isEnabled = $this->config->isEnabled();
        if ($isAllowed && $isEnabled) {
            $data = [
                'label' => __('Login as Customer'),
                'class' => 'login login-button',
                'on_click' => 'window.lacConfirmationPopup("'
                    . $this->escaper->escapeHtml($this->escaper->escapeJs($this->getLoginUrl()))
                    . '")',
                'sort_order' => 15,
            ];
        }

        return $data;
    }

    /**
     * Get Login as Customer login url.
     *
     * @return string
     */
    public function getLoginUrl(): string
    {
        return $this->getUrl('loginascustomer/login/login', ['customer_id' => $this->getCustomerId()]);
    }
}
