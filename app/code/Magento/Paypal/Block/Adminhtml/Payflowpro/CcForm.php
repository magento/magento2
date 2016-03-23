<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\Payflowpro;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;
use Magento\Vault\Model\VaultPaymentInterface;

class CcForm extends \Magento\Payment\Block\Transparent\Form
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Paypal::transparent/form.phtml';

    /**
     * @var VaultPaymentInterface
     */
    private $vaultService;

    /**
     * @param Context $context
     * @param Config $paymentConfig
     * @param Session $checkoutSession
     * @param VaultPaymentInterface $vaultService
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        Session $checkoutSession,
        VaultPaymentInterface $vaultService,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $checkoutSession, $data);
        $this->vaultService = $vaultService;
    }

    /**
     * Check if vault enabled
     * @return bool
     */
    public function isVaultEnabled()
    {
        return $this->vaultService->isActiveForPayment(
            \Magento\Paypal\Model\Config::METHOD_PAYFLOWPRO
        );
    }

    /**
     * On backend this block does not have any conditional checks
     *
     * @return bool
     */
    protected function shouldRender()
    {
        return true;
    }

    /**
     * {inheritdoc}
     */
    protected function initializeMethod()
    {
        return;
    }
}
