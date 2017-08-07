<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

/**
 * Class \Magento\Paypal\Block\Adminhtml\Payflowpro\CcForm
 *
 * @since 2.1.0
 */
class CcForm extends \Magento\Payment\Block\Transparent\Form
{
    /**
     * @var string
     * @since 2.1.0
     */
    protected $_template = 'Magento_Paypal::transparent/form.phtml';

    /**
     * @var Data
     * @since 2.1.0
     */
    private $paymentDataHelper;

    /**
     * @param Context $context
     * @param Config $paymentConfig
     * @param Session $checkoutSession
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $checkoutSession, $data);
    }

    /**
     * Check if vault enabled
     * @return bool
     * @since 2.1.0
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
     * @since 2.1.0
     */
    protected function shouldRender()
    {
        return true;
    }

    /**
     * {inheritdoc}
     * @since 2.1.0
     */
    protected function initializeMethod()
    {
        return;
    }

    /**
     * Get configured vault payment for PayflowPro
     * @return VaultPaymentInterface
     * @since 2.1.0
     */
    private function getVaultPayment()
    {
        return  $this->getPaymentDataHelper()->getMethodInstance(Transparent::CC_VAULT_CODE);
    }

    /**
     * Get payment data helper instance
     * @return Data
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getPaymentDataHelper()
    {
        if ($this->paymentDataHelper === null) {
            $this->paymentDataHelper = ObjectManager::getInstance()->get(Data::class);
        }
        return $this->paymentDataHelper;
    }
}
