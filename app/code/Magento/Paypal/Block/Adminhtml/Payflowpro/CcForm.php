<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Block\Adminhtml\Payflowpro;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Config;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Model\VaultPaymentInterface;

class CcForm extends \Magento\Payment\Block\Transparent\Form
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Paypal::transparent/form.phtml';

    /**
     * @var Data
     */
    private $paymentDataHelper;

    /**
     * @param Context $context
     * @param Config $paymentConfig
     * @param Session $checkoutSession
     * @param array $data
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        Session $checkoutSession,
        array $data = []
    ) {
        //phpcs:enable Generic.CodeAnalysis.UselessOverridingMethod
        parent::__construct($context, $paymentConfig, $checkoutSession, $data);
    }

    /**
     * Check if vault enabled
     *
     * @return bool
     */
    public function isVaultEnabled()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $vaultPayment = $this->getVaultPayment();
        return $vaultPayment->isActive($storeId);
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
     * @inheritDoc
     */
    protected function initializeMethod()
    {
        // @codingStandardsIgnoreStart
        return;
        // @codingStandardsIgnoreEnd
    }

    /**
     * Get configured vault payment for PayflowPro
     *
     * @return VaultPaymentInterface
     */
    private function getVaultPayment()
    {
        return  $this->getPaymentDataHelper()->getMethodInstance(Transparent::CC_VAULT_CODE);
    }

    /**
     * Get payment data helper instance
     *
     * @return Data
     * @deprecated 100.1.0
     * @see Nothing
     */
    private function getPaymentDataHelper()
    {
        if ($this->paymentDataHelper === null) {
            $this->paymentDataHelper = ObjectManager::getInstance()->get(Data::class);
        }
        return $this->paymentDataHelper;
    }
}
